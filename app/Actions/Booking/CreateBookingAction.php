<?php

namespace App\Actions\Booking;

use App\Enums\ApprovalStatus;
use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\BookingTourDetail;
use App\Models\BookingVehicleDetail;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateBookingAction
{
    /**
     * Create a new booking with immutable snapshots and 24-hour vendor SLA.
     *
     * @param  User  $customer
     * @param  Product  $product
     * @param  array  $data
     * @return Booking
     *
     * @throws InvalidArgumentException
     */
    public function execute(User $customer, Product $product, array $data): Booking
    {
        // 1. Validate Product Status
        if ($product->status !== ProductStatus::PUBLISHED || $product->approval_status !== ApprovalStatus::APPROVED) {
            throw new InvalidArgumentException('Produk tidak tersedia untuk dipesan saat ini.');
        }

        // 2. Validate Lead Time
        $startDate = Carbon::parse($data['service_start_date']);
        if (! empty($data['service_start_time'])) {
            $startDate = Carbon::parse($data['service_start_date'].' '.$data['service_start_time']);
        }

        $minLeadTimeHours = $product->min_lead_time_hours ?? ($product->product_type === ProductType::TOUR ? 24 : 12);
        $minimumAllowedTime = now()->addHours($minLeadTimeHours);

        if ($startDate->lt($minimumAllowedTime)) {
            $formattedHours = $minLeadTimeHours >= 24 ? ($minLeadTimeHours / 24).' hari' : $minLeadTimeHours.' jam';
            throw new InvalidArgumentException("Pemesanan produk ini wajib dilakukan minimal {$formattedHours} sebelum jadwal keberangkatan.");
        }

        // 3. Calculate Prices & Snapshots
        $quantity = 1;
        $totalPrice = (float) $product->base_price;
        $endDate = $startDate->copy();

        if ($product->product_type === ProductType::TOUR) {
            $quantity = (int) ($data['participant_count'] ?? 1);
            $totalPrice = (float) $product->base_price * $quantity;
            $durationDays = $product->tourPackage?->duration_days ?? 1;
            $endDate = $startDate->copy()->addDays($durationDays - 1);
        } elseif ($product->product_type === ProductType::VEHICLE) {
            $rentalDays = (int) ($data['rental_days'] ?? 1);
            $quantity = $rentalDays;
            $totalPrice = (float) $product->base_price * $rentalDays;
            $endDate = $startDate->copy()->addDays($rentalDays - 1);
        }

        $dpPercentage = (float) ($product->dp_percentage ?? 30.00);
        $dpAmount = round($totalPrice * ($dpPercentage / 100), 2);
        $balanceAmount = $totalPrice - $dpAmount;

        // Platform Commission (Default 10%)
        $commissionPercentage = 10.00;
        $platformCommissionAmount = round($totalPrice * ($commissionPercentage / 100), 2);
        $partnerPayoutAmount = $totalPrice - $platformCommissionAmount;

        // Freeze Snapshots
        $productSnapshot = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'product_type' => $product->product_type->value,
            'partner_id' => $product->partner_id,
            'partner_name' => $product->partner?->business_name,
            'partner_city' => $product->partner?->city,
            'base_price' => (float) $product->base_price,
            'pricing_type' => $product->pricing_type->value,
            'min_lead_time_hours' => $product->min_lead_time_hours,
            'tour_details' => $product->product_type === ProductType::TOUR && $product->tourPackage ? [
                'tour_type' => $product->tourPackage->tour_type->value,
                'duration_days' => $product->tourPackage->duration_days,
                'duration_nights' => $product->tourPackage->duration_nights,
                'includes_ferry' => (bool) $product->tourPackage->includes_ferry,
                'inclusions' => $product->tourPackage->inclusions,
                'exclusions' => $product->tourPackage->exclusions,
                'terms_conditions' => $product->tourPackage->terms_conditions,
            ] : null,
            'vehicle_details' => $product->product_type === ProductType::VEHICLE && $product->vehicle ? [
                'vehicle_type' => $product->vehicle->vehicle_type->value,
                'brand' => $product->vehicle->brand,
                'model' => $product->vehicle->model,
                'transmission' => $product->vehicle->transmission->value,
                'passenger_capacity' => $product->vehicle->passenger_capacity,
                'with_driver' => (bool) $product->vehicle->with_driver,
                'license_plate' => $product->vehicle->license_plate,
            ] : null,
        ];

        $pricingBreakdown = [
            'base_price' => (float) $product->base_price,
            'quantity' => $quantity,
            'quantity_unit' => $product->product_type === ProductType::TOUR ? 'orang' : 'hari',
            'subtotal' => $totalPrice,
            'dp_percentage' => $dpPercentage,
            'dp_amount' => $dpAmount,
            'balance_amount' => $balanceAmount,
        ];

        $cancellationPolicySnapshot = [
            'rules' => [
                ['min_days_before' => 7, 'refund_percentage' => 100, 'label' => '> 7 Hari: Refund 100% dari DP'],
                ['min_days_before' => 3, 'refund_percentage' => 50, 'label' => '3 - 7 Hari: Refund 50% dari DP'],
                ['min_days_before' => 0, 'refund_percentage' => 0, 'label' => '< 3 Hari: Tidak ada refund'],
            ],
            'calculated_on' => 'AMOUNT_PAID',
        ];

        // 4. Execute Transaction
        return DB::transaction(function () use (
            $customer,
            $product,
            $data,
            $startDate,
            $endDate,
            $totalPrice,
            $dpAmount,
            $commissionPercentage,
            $platformCommissionAmount,
            $partnerPayoutAmount,
            $productSnapshot,
            $pricingBreakdown,
            $cancellationPolicySnapshot
        ) {
            $bookingCode = 'TB-'.date('Ymd').'-'.strtoupper(Str::random(5));

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'partner_id' => $product->partner_id,
                'booking_status' => BookingStatus::PENDING_APPROVAL,
                'payment_status' => PaymentStatus::UNPAID,
                'inventory_status' => InventoryStatus::NONE,
                'service_start_date' => $startDate->toDateString(),
                'service_end_date' => $endDate->toDateString(),
                'service_start_time' => $data['service_start_time'] ?? null,
                'total_price' => $totalPrice,
                'dp_amount' => $dpAmount,
                'total_paid' => 0.00,
                'commission_percentage' => $commissionPercentage,
                'platform_commission_amount' => $platformCommissionAmount,
                'partner_payout_amount' => $partnerPayoutAmount,
                'product_snapshot' => $productSnapshot,
                'pricing_breakdown' => $pricingBreakdown,
                'cancellation_policy_snapshot' => $cancellationPolicySnapshot,
                'vendor_sla_deadline' => now()->addHours(24), // 24 Hours SLA
            ]);

            if ($product->product_type === ProductType::TOUR) {
                BookingTourDetail::create([
                    'booking_id' => $booking->id,
                    'tour_departure_id' => $data['tour_departure_id'] ?? null,
                    'participant_count' => $data['participant_count'] ?? 1,
                    'special_requests' => $data['special_requests'] ?? null,
                ]);
            } elseif ($product->product_type === ProductType::VEHICLE) {
                BookingVehicleDetail::create([
                    'booking_id' => $booking->id,
                    'vehicle_id' => $product->vehicle?->id,
                    'pickup_location' => $data['pickup_location'] ?? 'Parapat Hub',
                    'dropoff_location' => $data['dropoff_location'] ?? 'Parapat Hub',
                    'pickup_time' => $data['service_start_time'] ?? '08:00',
                    'driver_notes' => $data['driver_notes'] ?? null,
                ]);
            }

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $customer->id,
                'actor_type' => 'CUSTOMER',
                'previous_status' => 'NONE',
                'new_status' => BookingStatus::PENDING_APPROVAL->value,
                'reason' => 'Pengajuan booking baru oleh pelanggan.',
                'metadata' => [
                    'ip' => request()->ip(),
                    'sla_hours' => 24,
                ],
            ]);

            return $booking;
        });

        // WhatsApp Notification Dispatch
        try {
            app(\App\Services\WhatsAppService::class)->notifyVendorNewBooking($booking);
        } catch (\Throwable $e) {
            // Log and ignore to avoid blocking transaction
        }

        return $booking;
    }
}
