<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BookingStatusHistory;
use App\Models\Refund;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminRefundController extends Controller
{
    /**
     * Display a listing of refunds for admin queue.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'PENDING');

        $query = Refund::query()->with(['booking.user', 'booking.partner', 'processedByAdmin']);

        if ($statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($r) {
            return [
                'id' => $r->id,
                'booking_code' => $r->booking?->booking_code,
                'booking_id' => $r->booking_id,
                'customer_name' => $r->booking?->user?->name,
                'customer_phone' => $r->booking?->user?->phone,
                'partner_name' => $r->booking?->partner?->business_name,
                'calculated_refund_percentage' => (float) $r->calculated_refund_percentage,
                'refund_amount' => (float) $r->refund_amount,
                'bank_name' => $r->bank_name,
                'bank_account_number' => $r->bank_account_number,
                'bank_account_holder' => $r->bank_account_holder,
                'reason' => $r->reason,
                'status' => $r->status->value,
                'processed_by' => $r->processedByAdmin?->name,
                'processed_at' => $r->processed_at?->format('d M Y, H:i'),
                'created_at' => $r->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'pending' => Refund::where('status', RefundStatus::PENDING)->count(),
            'processed' => Refund::where('status', RefundStatus::PROCESSED)->count(),
            'rejected' => Refund::where('status', RefundStatus::REJECTED)->count(),
            'all' => Refund::count(),
        ];

        return Inertia::render('Admin/Refunds/Index', [
            'refunds' => $refunds,
            'counts' => $counts,
            'filters' => [
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Display the detailed view of a refund.
     */
    public function show(string $id): Response
    {
        $refund = Refund::with(['booking.user', 'booking.partner', 'processedByAdmin'])->findOrFail($id);
        $booking = $refund->booking;

        return Inertia::render('Admin/Refunds/Show', [
            'refund' => [
                'id' => $refund->id,
                'calculated_refund_percentage' => (float) $refund->calculated_refund_percentage,
                'refund_amount' => (float) $refund->refund_amount,
                'reason' => $refund->reason,
                'bank_name' => $refund->bank_name,
                'bank_account_number' => $refund->bank_account_number,
                'bank_account_holder' => $refund->bank_account_holder,
                'status' => $refund->status->value,
                'processed_by' => $refund->processedByAdmin?->name,
                'processed_at' => $refund->processed_at?->format('d M Y, H:i'),
                'created_at' => $refund->created_at->format('d M Y, H:i'),
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->user?->name,
                    'customer_email' => $booking->user?->email,
                    'customer_phone' => $booking->user?->phone,
                    'product_name' => $booking->product_snapshot['name'] ?? $booking->product?->name,
                    'total_price' => (float) $booking->total_price,
                    'total_paid' => (float) $booking->total_paid,
                    'service_start_date' => $booking->service_start_date->format('d M Y'),
                    'partner_name' => $booking->partner?->business_name,
                ],
            ],
        ]);
    }

    /**
     * Process refund: Transfer funds to customer bank account and update statuses.
     */
    public function process(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'transfer_reference' => ['required', 'string', 'min:3', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = $request->user();
        $refund = Refund::with('booking')->findOrFail($id);
        $booking = $refund->booking;

        $prevBookingStatus = $booking->booking_status->value;

        DB::transaction(function () use ($refund, $booking, $admin, $request, $prevBookingStatus) {
            $refund->status = RefundStatus::PROCESSED;
            $refund->processed_by = $admin->id;
            $refund->processed_at = now();
            if ($request->filled('note')) {
                $refund->reason = $refund->reason." [Catatan Admin: {$request->note}]";
            }
            $refund->save();

            $booking->booking_status = BookingStatus::REFUNDED;
            $booking->payment_status = PaymentStatus::REFUNDED;
            $booking->save();

            $formattedAmount = number_format((float) $refund->refund_amount, 0, ',', '.');

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $admin->id,
                'actor_type' => 'ADMIN',
                'previous_status' => $prevBookingStatus,
                'new_status' => BookingStatus::REFUNDED->value,
                'reason' => "Admin mentransfer pengembalian dana senilai Rp {$formattedAmount} ke {$refund->bank_name} ({$refund->bank_account_number} a/n {$refund->bank_account_holder}). Ref Bank: {$request->transfer_reference}.",
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'REFUND_PROCESSED',
                'entity_type' => Refund::class,
                'entity_id' => $refund->id,
                'old_values' => ['status' => RefundStatus::PENDING->value],
                'new_values' => [
                    'status' => RefundStatus::PROCESSED->value,
                    'transfer_reference' => $request->transfer_reference,
                    'processed_at' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.refunds.index')
            ->with('success', "Pengembalian dana untuk pesanan {$booking->booking_code} berhasil diproses!");
    }

    /**
     * Reject refund request with mandatory explanation.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $admin = $request->user();
        $refund = Refund::with('booking')->findOrFail($id);
        $booking = $refund->booking;

        DB::transaction(function () use ($refund, $booking, $admin, $request) {
            $refund->status = RefundStatus::REJECTED;
            $refund->processed_by = $admin->id;
            $refund->processed_at = now();
            $refund->reason = $refund->reason." [Ditolak: {$request->reason}]";
            $refund->save();

            $booking->booking_status = BookingStatus::CANCELLED;
            $booking->save();

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $admin->id,
                'actor_type' => 'ADMIN',
                'previous_status' => BookingStatus::REFUND_PENDING->value,
                'new_status' => BookingStatus::CANCELLED->value,
                'reason' => "Pengajuan refund ditolak oleh Admin: {$request->reason}",
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'REFUND_REJECTED',
                'entity_type' => Refund::class,
                'entity_id' => $refund->id,
                'old_values' => ['status' => RefundStatus::PENDING->value],
                'new_values' => ['status' => RefundStatus::REJECTED->value, 'reason' => $request->reason],
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.refunds.index')
            ->with('success', "Pengajuan refund untuk pesanan {$booking->booking_code} telah ditolak.");
    }
}

