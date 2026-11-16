<?php

namespace App\Http\Controllers\Partner;

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\InventoryHold;
use App\Services\AppLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PartnerBookingController extends Controller
{
    /**
     * Display a listing of incoming booking requests for the partner.
     */
    public function index(Request $request): Response
    {
        $partner = $request->user()->currentPartner();
        $statusFilter = $request->query('status', 'PENDING_APPROVAL');

        $query = Booking::where('partner_id', $partner->id)
            ->with(['user', 'product', 'tourDetail', 'vehicleDetail', 'originalPartner']);

        if ($statusFilter !== 'ALL') {
            $query->where('booking_status', $statusFilter);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($b) {
            $remainingHours = null;
            if (in_array($b->booking_status, [BookingStatus::PENDING_APPROVAL, BookingStatus::ASSIGNED_TO_PARTNER]) && $b->vendor_sla_deadline) {
                $remainingHours = round(now()->diffInMinutes($b->vendor_sla_deadline, false) / 60, 1);
            }

            return [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'customer_name' => $b->user?->name,
                'customer_phone' => $b->user?->phone,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                'product_type' => $b->product_snapshot['product_type'] ?? 'TOUR',
                'service_start_date' => $b->service_start_date->format('d M Y'),
                'service_start_time' => $b->service_start_time,
                'total_price' => (float) $b->total_price,
                'dp_amount' => (float) $b->dp_amount,
                'booking_status' => $b->booking_status->value,
                'booking_status_label' => $b->booking_status->label(),
                'payment_status' => $b->payment_status->value,
                'is_platform_assigned' => $b->booking_status === BookingStatus::ASSIGNED_TO_PARTNER || ($b->original_partner_id && $b->original_partner_id !== $b->partner_id),
                'original_partner_name' => $b->originalPartner?->business_name,
                'vendor_sla_deadline' => $b->vendor_sla_deadline?->toIso8601String(),
                'remaining_sla_hours' => $remainingHours,
                'is_sla_expired' => $remainingHours !== null && $remainingHours <= 0,
                'created_at' => $b->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'pending_approval' => Booking::where('partner_id', $partner->id)->where('booking_status', BookingStatus::PENDING_APPROVAL)->count(),
            'assigned_to_partner' => Booking::where('partner_id', $partner->id)->where('booking_status', BookingStatus::ASSIGNED_TO_PARTNER)->count(),
            'awaiting_payment' => Booking::where('partner_id', $partner->id)->where('booking_status', BookingStatus::AWAITING_PAYMENT)->count(),
            'confirmed' => Booking::where('partner_id', $partner->id)->where('booking_status', BookingStatus::CONFIRMED)->count(),
            'all' => Booking::where('partner_id', $partner->id)->count(),
        ];

        return Inertia::render('Partner/Bookings/Index', [
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->business_name,
            ],
            'bookings' => $bookings,
            'counts' => $counts,
            'filters' => [
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Display a specific booking detail for partner operational review.
     */
    public function show(Request $request, string $id): Response
    {
        $partner = $request->user()->currentPartner();

        $booking = Booking::where('partner_id', $partner->id)
            ->with(['user', 'product', 'tourDetail', 'vehicleDetail', 'statusHistories.actor'])
            ->findOrFail($id);

        $remainingHours = null;
        if ($booking->booking_status === BookingStatus::PENDING_APPROVAL && $booking->vendor_sla_deadline) {
            $remainingHours = round(now()->diffInMinutes($booking->vendor_sla_deadline, false) / 60, 1);
        }

        return Inertia::render('Partner/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer' => [
                    'name' => $booking->user?->name,
                    'email' => $booking->user?->email,
                    'phone' => $booking->user?->phone,
                ],
                'product_snapshot' => $booking->product_snapshot,
                'pricing_breakdown' => $booking->pricing_breakdown,
                'booking_status' => $booking->booking_status->value,
                'booking_status_label' => $booking->booking_status->label(),
                'payment_status' => $booking->payment_status->value,
                'inventory_status' => $booking->inventory_status->value,
                'service_start_date' => $booking->service_start_date->format('d M Y'),
                'service_end_date' => $booking->service_end_date->format('d M Y'),
                'service_start_time' => $booking->service_start_time,
                'total_price' => (float) $booking->total_price,
                'dp_amount' => (float) $booking->dp_amount,
                'partner_payout_amount' => (float) $booking->partner_payout_amount,
                'vendor_sla_deadline' => $booking->vendor_sla_deadline?->toIso8601String(),
                'remaining_sla_hours' => $remainingHours,
                'is_sla_expired' => $remainingHours !== null && $remainingHours <= 0,
                'tour_detail' => $booking->tourDetail ? [
                    'participant_count' => $booking->tourDetail->participant_count,
                    'special_requests' => $booking->tourDetail->special_requests,
                ] : null,
                'vehicle_detail' => $booking->vehicleDetail ? [
                    'pickup_location' => $booking->vehicleDetail->pickup_location,
                    'dropoff_location' => $booking->vehicleDetail->dropoff_location,
                    'driver_notes' => $booking->vehicleDetail->driver_notes,
                ] : null,
                'histories' => $booking->statusHistories->map(fn ($h) => [
                    'id' => $h->id,
                    'actor_name' => $h->actor?->name ?? 'Sistem Muter-Muter',
                    'previous_status' => $h->previous_status,
                    'new_status' => $h->new_status,
                    'reason' => $h->reason,
                    'created_at' => $h->created_at->format('d M Y, H:i'),
                ]),
            ],
        ]);
    }

    /**
     * Partner 1-click approves availability: unlocks DP bill and triggers 24h inventory hold.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $partner = $request->user()->currentPartner();
        $user = $request->user();

        $booking = Booking::where('partner_id', $partner->id)->findOrFail($id);

        if (! in_array($booking->booking_status, [BookingStatus::PENDING_APPROVAL, BookingStatus::ASSIGNED_TO_PARTNER, BookingStatus::MANUAL_REVIEW])) {
            return back()->with('error', 'Pesanan ini tidak dalam status menunggu persetujuan.');
        }

        $prevStatus = $booking->booking_status->value;

        DB::transaction(function () use ($booking, $user, $prevStatus) {
            $booking->booking_status = BookingStatus::AWAITING_PAYMENT;
            $booking->inventory_status = InventoryStatus::HELD;
            $booking->save();

            // 24-hour Temporary Inventory Hold
            InventoryHold::create([
                'booking_id' => $booking->id,
                'product_id' => $booking->product_id,
                'tour_departure_id' => $booking->tourDetail?->tour_departure_id,
                'vehicle_id' => $booking->vehicleDetail?->vehicle_id,
                'held_quantity' => $booking->tourDetail?->participant_count ?? 1,
                'held_at' => now(),
                'expires_at' => now()->addHours(24),
                'status' => InventoryStatus::HELD,
            ]);

            $approvalReason = $prevStatus === BookingStatus::ASSIGNED_TO_PARTNER->value
                ? 'Mitra menyetujui penugasan dari platform. Tagihan DP dan hold inventory 24 jam aktif.'
                : 'Mitra menyetujui ketersediaan jadwal. Tagihan DP dan hold inventory 24 jam aktif.';

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $user->id,
                'actor_type' => 'PARTNER',
                'previous_status' => $prevStatus,
                'new_status' => BookingStatus::AWAITING_PAYMENT->value,
                'reason' => $approvalReason,
            ]);
        });

        // WhatsApp Notification to Customer
        try {
            app(\App\Services\WhatsAppService::class)->notifyCustomerBookingApproved($booking);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        // Structured Logging
        AppLogger::booking('booking.vendor.approved', "Mitra menyetujui pesanan [{$booking->booking_code}]", [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'partner_id' => $partner->id,
            'actor_id' => $user->id,
            'hold_expires_at' => now()->addHours(24)->toIso8601String(),
        ]);

        return redirect()->route('partner.bookings.index')
            ->with('success', "Pesanan {$booking->booking_code} disetujui! Customer kini dapat membayar DP dalam 24 jam.");
    }

    /**
     * Partner rejects booking availability with explanation, routing to Admin for fallback dispatching.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $partner = $request->user()->currentPartner();
        $user = $request->user();

        $booking = Booking::where('partner_id', $partner->id)->findOrFail($id);

        if (! in_array($booking->booking_status, [BookingStatus::PENDING_APPROVAL, BookingStatus::ASSIGNED_TO_PARTNER, BookingStatus::MANUAL_REVIEW])) {
            return back()->with('error', 'Pesanan ini tidak dalam status menunggu persetujuan.');
        }

        $prevStatus = $booking->booking_status->value;

        DB::transaction(function () use ($booking, $user, $prevStatus, $request, $partner) {
            // Route to Admin MANUAL_REVIEW queue to salvage booking instead of cancelling immediately
            $booking->booking_status = BookingStatus::MANUAL_REVIEW;
            $booking->rejection_reason = $request->reason;
            $booking->save();

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $user->id,
                'actor_type' => 'PARTNER',
                'previous_status' => $prevStatus,
                'new_status' => BookingStatus::MANUAL_REVIEW->value,
                'reason' => "Mitra {$partner->business_name} tidak dapat melayani pesanan: {$request->reason}. Dialihkan ke antrean Admin untuk dicarikan mitra pengganti.",
            ]);
        });

        // Structured Logging
        AppLogger::booking('booking.vendor.rejected', "Mitra menolak pesanan [{$booking->booking_code}], dialihkan ke antrean Admin: {$request->reason}", [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'partner_id' => $partner->id,
            'actor_id' => $user->id,
            'reason' => $request->reason,
        ]);

        return redirect()->route('partner.bookings.index')
            ->with('success', "Pesanan {$booking->booking_code} telah ditolak dan dialihkan ke tim Admin untuk dicarikan mitra alternatif.");
    }
}
