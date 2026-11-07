<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\PaymentStatus;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\InventoryHold;
use App\Models\Payment;
use App\Models\VendorSettlement;
use App\Services\AppLogger;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of payments for admin verification.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'PAYMENT_REVIEW');

        $query = Payment::query()->with(['booking.user', 'booking.partner', 'latestProof']);

        if ($statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15)->through(function ($p) {
            return [
                'id' => $p->id,
                'payment_code' => $p->payment_code,
                'booking_code' => $p->booking?->booking_code,
                'booking_id' => $p->booking_id,
                'customer_name' => $p->booking?->user?->name,
                'customer_phone' => $p->booking?->user?->phone,
                'partner_name' => $p->booking?->partner?->business_name,
                'amount' => (float) $p->amount,
                'payment_type' => $p->payment_type,
                'status' => $p->status,
                'proof_url' => $p->latestProof?->file_path,
                'bank_sender' => $p->latestProof ? "{$p->latestProof->bank_sender_name} ({$p->latestProof->bank_sender_account})" : '-',
                'transferred_at' => $p->latestProof?->transferred_at?->format('d M Y, H:i') ?? $p->created_at->format('d M Y, H:i'),
                'created_at' => $p->created_at->format('d M Y, H:i'),
            ];
        });

        $counts = [
            'payment_review' => Payment::where('status', 'PAYMENT_REVIEW')->count(),
            'verified' => Payment::where('status', 'VERIFIED')->count(),
            'rejected' => Payment::where('status', 'REJECTED')->count(),
            'all' => Payment::count(),
        ];

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'counts' => $counts,
            'filters' => [
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Display the detailed view of a payment and its transfer proof.
     */
    public function show(string $id): Response
    {
        $payment = Payment::query()
            ->with(['booking.user', 'booking.partner', 'proofs.verifiedByAdmin'])
            ->findOrFail($id);

        $booking = $payment->booking;

        return Inertia::render('Admin/Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'payment_code' => $payment->payment_code,
                'payment_type' => $payment->payment_type,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->format('d M Y, H:i'),
                'created_at' => $payment->created_at->format('d M Y, H:i'),
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->user?->name,
                    'customer_email' => $booking->user?->email,
                    'customer_phone' => $booking->user?->phone,
                    'product_name' => $booking->product_snapshot['name'] ?? $booking->product?->name,
                    'total_price' => (float) $booking->total_price,
                    'dp_amount' => (float) $booking->dp_amount,
                    'service_start_date' => $booking->service_start_date->format('d M Y'),
                    'partner_name' => $booking->partner?->business_name,
                ],
                'proofs' => $payment->proofs->map(fn ($pr) => [
                    'id' => $pr->id,
                    'file_path' => $pr->file_path,
                    'bank_sender_name' => $pr->bank_sender_name,
                    'bank_sender_account' => $pr->bank_sender_account,
                    'bank_destination_name' => $pr->bank_destination_name,
                    'transferred_at' => $pr->transferred_at?->format('d M Y, H:i'),
                    'verified_by' => $pr->verifiedByAdmin?->name,
                    'verified_at' => $pr->verified_at?->format('d M Y, H:i'),
                    'verification_note' => $pr->verification_note,
                ]),
            ],
        ]);
    }

    /**
     * Approve customer payment proof and confirm booking.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $admin = $request->user();
        $payment = Payment::with(['booking', 'latestProof'])->findOrFail($id);
        $booking = $payment->booking;

        $prevBookingStatus = $booking->booking_status->value;
        $prevPaymentStatus = $booking->payment_status->value;

        DB::transaction(function () use ($payment, $booking, $admin, $prevBookingStatus, $prevPaymentStatus) {
            $payment->status = 'VERIFIED';
            $payment->paid_at = now();
            $payment->save();

            if ($payment->latestProof) {
                $payment->latestProof->verified_by = $admin->id;
                $payment->latestProof->verified_at = now();
                $payment->latestProof->save();
            }

            // Update Booking status to CONFIRMED and inventory to ALLOCATED
            $booking->booking_status = BookingStatus::CONFIRMED;
            $booking->payment_status = PaymentStatus::PARTIALLY_PAID;
            $booking->inventory_status = InventoryStatus::ALLOCATED;
            $booking->total_paid = $payment->amount;
            $booking->save();

            // Update InventoryHold to ALLOCATED
            InventoryHold::where('booking_id', $booking->id)
                ->where('status', InventoryStatus::HELD)
                ->update(['status' => InventoryStatus::ALLOCATED]);

            // Create Vendor Settlement in PENDING status if booking belongs to a partner
            if ($booking->partner_id) {
                VendorSettlement::firstOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'partner_id' => $booking->partner_id,
                        'gross_amount' => $booking->total_price,
                        'commission_amount' => $booking->platform_commission_amount,
                        'net_payout_amount' => $booking->partner_payout_amount,
                        'status' => SettlementStatus::PENDING,
                    ]
                );
            }

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $admin->id,
                'actor_type' => 'ADMIN',
                'previous_status' => $prevBookingStatus,
                'new_status' => BookingStatus::CONFIRMED->value,
                'reason' => 'Admin memverifikasi bukti transfer DP senilai Rp '.number_format((float) $payment->amount, 0, ',', '.').'. Status pesanan resmi TERKONFIRMASI dan unit teralokasi pasti.',
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'PAYMENT_VERIFIED',
                'entity_type' => Payment::class,
                'entity_id' => $payment->id,
                'old_values' => ['status' => 'PAYMENT_REVIEW'],
                'new_values' => ['status' => 'VERIFIED', 'paid_at' => now()->toIso8601String()],
                'ip_address' => request()->ip(),
            ]);
        });

        // Trigger WhatsApp notification to customer
        try {
            app(WhatsAppService::class)->notifyCustomerPaymentConfirmed($booking->fresh(['user', 'partner']));
        } catch (\Throwable $e) {
            // Log and avoid blocking user response if notification fails
            \Illuminate\Support\Facades\Log::warning('WhatsApp confirmation notification failed: '.$e->getMessage());
        }

        // Structured Logging
        AppLogger::payment('payment.proof.verified', "Pembayaran diverifikasi untuk pesanan [{$booking->booking_code}] senilai Rp {$payment->amount}", [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'payment_id' => $payment->id,
            'amount' => (float) $payment->amount,
            'admin_id' => $admin->id,
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', "Pembayaran untuk pesanan {$booking->booking_code} berhasil diverifikasi! Pesanan kini berstatus Terkonfirmasi.");
    }

    /**
     * Reject customer payment proof with feedback reason.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $admin = $request->user();
        $payment = Payment::with(['booking', 'latestProof'])->findOrFail($id);
        $booking = $payment->booking;

        $prevBookingStatus = $booking->booking_status->value;

        DB::transaction(function () use ($payment, $booking, $admin, $prevBookingStatus, $request) {
            $payment->status = 'REJECTED';
            $payment->save();

            if ($payment->latestProof) {
                $payment->latestProof->verified_by = $admin->id;
                $payment->latestProof->verified_at = now();
                $payment->latestProof->verification_note = $request->reason;
                $payment->latestProof->save();
            }

            // Return booking to AWAITING_PAYMENT so customer can re-upload valid proof
            $booking->booking_status = BookingStatus::AWAITING_PAYMENT;
            $booking->payment_status = PaymentStatus::UNPAID;
            $booking->save();

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $admin->id,
                'actor_type' => 'ADMIN',
                'previous_status' => $prevBookingStatus,
                'new_status' => BookingStatus::AWAITING_PAYMENT->value,
                'reason' => 'Bukti transfer DP ditolak oleh Admin: '.$request->reason,
            ]);

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'PAYMENT_REJECTED',
                'entity_type' => Payment::class,
                'entity_id' => $payment->id,
                'old_values' => ['status' => 'PAYMENT_REVIEW'],
                'new_values' => ['status' => 'REJECTED', 'reason' => $request->reason],
                'ip_address' => request()->ip(),
            ]);
        });

        // Structured Logging
        AppLogger::payment('payment.proof.rejected', "Bukti pembayaran ditolak untuk pesanan [{$booking->booking_code}]", [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'payment_id' => $payment->id,
            'reason' => $request->reason,
            'admin_id' => $admin->id,
        ], level: 'warning');

        return redirect()->route('admin.payments.index')
            ->with('success', "Bukti transfer untuk pesanan {$booking->booking_code} telah ditolak dengan catatan evaluasi.");
    }
}
