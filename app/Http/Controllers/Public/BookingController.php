<?php

namespace App\Http\Controllers\Public;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\InventoryHold;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display a specific booking detail.
     */
    public function show(Request $request, string $code): Response
    {
        $user = $request->user();

        $booking = Booking::query()
            ->with(['product', 'partner', 'statusHistories.actor', 'payments.proofs', 'refunds'])
            ->where('booking_code', $code)
            ->firstOrFail();

        // Security check: Only booking owner or admin can view
        if ($user && ! $user->isAdmin() && $booking->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat rincian pemesanan ini.');
        }

        // Active inventory hold check
        $activeHold = InventoryHold::where('booking_id', $booking->id)
            ->where('expires_at', '>', now())
            ->first();

        $departure = $booking->service_start_date->copy()->startOfDay();
        $hoursUntilDeparture = now()->diffInHours($departure, false);
        $estimatedRefundPercentage = $hoursUntilDeparture > 168 ? 100 : ($hoursUntilDeparture >= 72 ? 50 : 0);
        $estimatedRefundAmount = (float) $booking->total_paid > 0 ? round((float) $booking->total_paid * ($estimatedRefundPercentage / 100), 2) : 0;
        $isCancellable = ! in_array($booking->booking_status->value, ['COMPLETED', 'CANCELLED', 'EXPIRED', 'REFUNDED'], true);

        $latestRefund = $booking->refunds->last();

        return Inertia::render('Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'status' => $booking->booking_status->value,
                'status_label' => $booking->booking_status->label(),
                'payment_status' => $booking->payment_status->value,
                'payment_status_label' => $booking->payment_status->label(),
                'inventory_status' => $booking->inventory_status->value,
                'inventory_status_label' => $booking->inventory_status->label(),
                'service_start_date' => $booking->service_start_date->format('d M Y'),
                'service_end_date' => $booking->service_end_date->format('d M Y'),
                'service_start_time' => $booking->service_start_time,
                'total_price' => (float) $booking->total_price,
                'dp_amount' => (float) $booking->dp_amount,
                'total_paid' => (float) $booking->total_paid,
                'balance_amount' => (float) $booking->total_price - (float) $booking->dp_amount,
                'vendor_sla_deadline' => $booking->vendor_sla_deadline?->toIso8601String(),
                'is_sla_expired' => $booking->vendor_sla_deadline && $booking->vendor_sla_deadline->isPast(),
                'created_at' => $booking->created_at->format('d M Y, H:i'),
                'product_snapshot' => $booking->product_snapshot,
                'pricing_breakdown' => $booking->pricing_breakdown,
                'cancellation_policy' => $booking->cancellation_policy_snapshot,
                'partner' => [
                    'name' => $booking->partner?->business_name,
                    'pic_phone' => $booking->partner?->pic_phone,
                    'city' => $booking->partner?->city,
                ],
                'active_hold_expires_at' => $activeHold?->expires_at?->toIso8601String(),
                'histories' => $booking->statusHistories->map(fn ($h) => [
                    'id' => $h->id,
                    'actor_type' => $h->actor_type,
                    'actor_name' => $h->actor?->name ?? 'Sistem Muter-Muter',
                    'previous_status' => $h->previous_status,
                    'new_status' => $h->new_status,
                    'reason' => $h->reason,
                    'created_at' => $h->created_at->format('d M Y, H:i'),
                ]),
                'payment_proof' => $booking->payments->first()?->proofs->first() ? [
                    'id' => $booking->payments->first()->proofs->first()->id,
                    'proof_url' => $booking->payments->first()->proofs->first()->proof_url,
                    'uploaded_at' => $booking->payments->first()->proofs->first()->created_at->format('d M Y, H:i'),
                ] : null,
                'is_cancellable' => $isCancellable,
                'hours_until_departure' => $hoursUntilDeparture,
                'estimated_refund_percentage' => $estimatedRefundPercentage,
                'estimated_refund_amount' => $estimatedRefundAmount,
                'latest_refund' => $latestRefund ? [
                    'id' => $latestRefund->id,
                    'status' => $latestRefund->status->value,
                    'refund_amount' => (float) $latestRefund->refund_amount,
                    'calculated_percentage' => (float) $latestRefund->calculated_refund_percentage,
                    'bank_name' => $latestRefund->bank_name,
                    'bank_account_number' => $latestRefund->bank_account_number,
                    'bank_account_holder' => $latestRefund->bank_account_holder,
                    'processed_at' => $latestRefund->processed_at?->format('d M Y, H:i'),
                ] : null,
            ],
            'bankAccount' => [
                'bank_name' => 'Bank Mandiri',
                'account_number' => '107-00-1928374-1',
                'account_holder' => 'PT MUTER MUTER WISATA TOBA',
            ],
        ]);
    }

    /**
     * Customer bookings list.
     */
    public function myBookings(Request $request): Response
    {
        $user = $request->user();

        $bookings = Booking::where('user_id', $user->id)
            ->with(['product', 'partner'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                'product_type' => $b->product_snapshot['product_type'] ?? 'TOUR',
                'partner_name' => $b->partner?->business_name,
                'service_start_date' => $b->service_start_date->format('d M Y'),
                'total_price' => (float) $b->total_price,
                'dp_amount' => (float) $b->dp_amount,
                'booking_status' => $b->booking_status->value,
                'booking_status_label' => $b->booking_status->label(),
                'payment_status' => $b->payment_status->value,
                'created_at' => $b->created_at->format('d M Y'),
            ]);

        return Inertia::render('Bookings/MyBookings', [
            'bookings' => $bookings,
        ]);
    }
}
