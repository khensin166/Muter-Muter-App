<?php

namespace App\Http\Controllers;

use App\Actions\Booking\CancelBookingAction;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingCancellationController extends Controller
{
    /**
     * Handle customer cancellation request.
     */
    public function cancel(Request $request, string $bookingCode): RedirectResponse
    {
        $user = $request->user();
        $booking = Booking::with(['partner', 'user'])->where('booking_code', $bookingCode)->firstOrFail();

        // Customer authorization
        if ($booking->user_id !== $user->id && $user->role->value !== 'ADMIN') {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan pesanan ini.');
        }

        $departureDate = $booking->service_start_date->copy()->startOfDay();
        $hoursUntilDeparture = now()->diffInHours($departureDate, false);
        $isEligibleForRefund = ((float) $booking->total_paid > 0) && ($hoursUntilDeparture >= 72);

        $rules = [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];

        if ($isEligibleForRefund) {
            $rules['bank_name'] = ['required', 'string', 'max:100'];
            $rules['bank_account_number'] = ['required', 'string', 'max:100'];
            $rules['bank_account_holder'] = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $bankInfo = $isEligibleForRefund ? [
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
        ] : null;

        $action = new CancelBookingAction();
        $result = $action->execute($booking, $user, $validated['reason'], $bankInfo);

        $refundAmount = $result['refund_amount'];
        $formattedRefund = number_format($refundAmount, 0, ',', '.');

        if ($refundAmount > 0) {
            $msg = "Pesanan {$booking->booking_code} berhasil dibatalkan. Pengajuan pengembalian dana sebesar Rp {$formattedRefund} ({$result['refund_percentage']}%) sedang diproses oleh admin.";
        } else {
            $msg = "Pesanan {$booking->booking_code} telah resmi dibatalkan.";
        }

        return redirect()->route('bookings.show', $booking->booking_code)->with('success', $msg);
    }
}

