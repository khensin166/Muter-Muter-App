<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\InventoryHold;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminSlaController extends Controller
{
    /**
     * Display the SLA monitoring and manual review console.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'MANUAL_REVIEW');

        $query = Booking::query()->with(['user', 'product', 'partner', 'tourDetail', 'vehicleDetail']);

        if ($statusFilter !== 'ALL') {
            $query->where('booking_status', $statusFilter);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($b) {
            $remainingHours = null;
            if ($b->vendor_sla_deadline) {
                $remainingHours = round(now()->diffInMinutes($b->vendor_sla_deadline, false) / 60, 1);
            }

            return [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'customer_name' => $b->user?->name,
                'customer_phone' => $b->user?->phone,
                'partner_name' => $b->partner?->business_name,
                'partner_phone' => $b->partner?->pic_phone,
                'partner_city' => $b->partner?->city,
                'product_name' => $b->product_snapshot['name'] ?? $b->product?->name,
                'product_type' => $b->product_snapshot['product_type'] ?? 'TOUR',
                'service_start_date' => $b->service_start_date->format('d M Y'),
                'total_price' => (float) $b->total_price,
                'dp_amount' => (float) $b->dp_amount,
                'booking_status' => $b->booking_status->value,
                'booking_status_label' => $b->booking_status->label(),
                'payment_status' => $b->payment_status->value,
                'vendor_sla_deadline' => $b->vendor_sla_deadline?->toIso8601String(),
                'remaining_sla_hours' => $remainingHours,
                'is_sla_expired' => $remainingHours !== null && $remainingHours <= 0,
                'created_at' => $b->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'manual_review' => Booking::where('booking_status', BookingStatus::MANUAL_REVIEW)->count(),
            'pending_approval' => Booking::where('booking_status', BookingStatus::PENDING_APPROVAL)->count(),
            'all' => Booking::count(),
        ];

        return Inertia::render('Admin/Sla/Index', [
            'bookings' => $bookings,
            'counts' => $counts,
            'filters' => [
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Admin force-approves booking on behalf of vendor.
     */
    public function forceApprove(Request $request, string $id): RedirectResponse
    {
        $admin = $request->user();
        $booking = Booking::findOrFail($id);

        $prevStatus = $booking->booking_status->value;

        DB::transaction(function () use ($booking, $admin, $prevStatus) {
            $booking->booking_status = BookingStatus::AWAITING_PAYMENT;
            $booking->inventory_status = InventoryStatus::HELD;
            $booking->save();

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

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $admin->id,
                'actor_type' => 'ADMIN',
                'previous_status' => $prevStatus,
                'new_status' => BookingStatus::AWAITING_PAYMENT->value,
                'reason' => 'Intervensi Admin: Persetujuan manual diberikan karena batas SLA vendor habis. Tagihan DP dan hold inventory 24 jam dibuka untuk customer.',
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'BOOKING_FORCE_APPROVED',
                'entity_type' => Booking::class,
                'entity_id' => $booking->id,
                'old_values' => ['booking_status' => $prevStatus],
                'new_values' => ['booking_status' => BookingStatus::AWAITING_PAYMENT->value],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.sla.index')
            ->with('success', "Pesanan {$booking->booking_code} berhasil disetujui secara manual oleh Admin.");
    }

    /**
     * Admin cancels unresponded booking.
     */
    public function forceCancel(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $admin = $request->user();
        $booking = Booking::findOrFail($id);

        $prevStatus = $booking->booking_status->value;

        DB::transaction(function () use ($booking, $admin, $prevStatus, $request) {
            $booking->booking_status = BookingStatus::CANCELLED;
            $booking->inventory_status = InventoryStatus::RELEASED;
            $booking->save();

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $admin->id,
                'actor_type' => 'ADMIN',
                'previous_status' => $prevStatus,
                'new_status' => BookingStatus::CANCELLED->value,
                'reason' => 'Intervensi Admin: Pemesanan dibatalkan karena: '.$request->reason,
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'BOOKING_FORCE_CANCELLED',
                'entity_type' => Booking::class,
                'entity_id' => $booking->id,
                'old_values' => ['booking_status' => $prevStatus],
                'new_values' => ['booking_status' => BookingStatus::CANCELLED->value, 'reason' => $request->reason],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.sla.index')
            ->with('success', "Pesanan {$booking->booking_code} telah dibatalkan oleh Admin.");
    }
}

