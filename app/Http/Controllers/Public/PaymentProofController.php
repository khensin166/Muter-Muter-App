<?php

namespace App\Http\Controllers\Public;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Services\FileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentProofController extends Controller
{
    /**
     * Store uploaded customer payment proof.
     */
    public function store(Request $request, string $code): RedirectResponse
    {
        $user = $request->user();

        $booking = Booking::where('booking_code', $code)->firstOrFail();

        // Customer access authorization
        if ($user && ! $user->isAdmin() && $booking->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($booking->booking_status, [BookingStatus::AWAITING_PAYMENT, BookingStatus::MANUAL_REVIEW])) {
            return back()->with('error', 'Pesanan ini tidak dalam status menunggu pembayaran DP.');
        }

        $validated = $request->validate([
            'proof_file' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
            'bank_sender_name' => ['required', 'string', 'max:100'],
            'bank_sender_account' => ['required', 'string', 'max:50'],
            'transferred_at' => ['nullable', 'date'],
        ]);

        // Deep 4-layer file inspection, EXIF stripping & UUID storage
        $path = FileSecurityService::validateAndStore(
            file: $request->file('proof_file'),
            directory: 'payments/proofs',
            fieldName: 'proof_file',
            allowPdf: true,
            disk: 'public'
        );

        DB::transaction(function () use ($booking, $validated, $path, $user) {

            // Find or create Payment record
            $payment = Payment::firstOrCreate(
                [
                    'booking_id' => $booking->id,
                    'payment_type' => 'DOWN_PAYMENT',
                ],
                [
                    'payment_code' => 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(5)),
                    'payment_method' => 'MANUAL_BANK_TRANSFER',
                    'amount' => $booking->dp_amount,
                    'status' => 'PAYMENT_REVIEW',
                ]
            );

            $payment->status = 'PAYMENT_REVIEW';
            $payment->save();

            // Create PaymentProof
            PaymentProof::create([
                'payment_id' => $payment->id,
                'file_path' => '/storage/'.$path,
                'bank_sender_name' => $validated['bank_sender_name'],
                'bank_sender_account' => $validated['bank_sender_account'],
                'bank_destination_name' => 'Bank Mandiri (107-00-1928374-1)',
                'transferred_at' => $validated['transferred_at'] ?? now(),
            ]);

            // Update Booking Status
            $prevBookingStatus = $booking->booking_status->value;
            $prevPaymentStatus = $booking->payment_status->value;

            $booking->booking_status = BookingStatus::PAYMENT_REVIEW;
            $booking->payment_status = PaymentStatus::PAYMENT_REVIEW;
            $booking->save();

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $user?->id,
                'actor_type' => 'CUSTOMER',
                'previous_status' => $prevBookingStatus,
                'new_status' => BookingStatus::PAYMENT_REVIEW->value,
                'reason' => 'Pelanggan mengunggah bukti transfer DP dari rekening '.$validated['bank_sender_name'].' ('.$validated['bank_sender_account'].'). Menunggu verifikasi admin.',
            ]);
        });

        return redirect()->route('bookings.show', $booking->booking_code)
            ->with('success', 'Bukti transfer DP berhasil diunggah! Tim Admin sedang memverifikasi pembayaran Anda.');
    }
}

