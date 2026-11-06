<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\SettlementStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\InventoryHold;
use App\Models\Refund;
use App\Models\User;
use App\Models\VendorSettlement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancelBookingAction
{
    /**
     * Cancel an active booking and calculate refund based on immutability snapshot policy.
     *
     * @param Booking $booking
     * @param User $actor
     * @param string $reason
     * @param array|null $bankInfo
     * @return array
     */
    public function execute(Booking $booking, User $actor, string $reason, ?array $bankInfo = null): array
    {
        $nonCancellableStatuses = [
            BookingStatus::COMPLETED,
            BookingStatus::CANCELLED,
            BookingStatus::EXPIRED,
            BookingStatus::REFUNDED,
        ];

        if (in_array($booking->booking_status, $nonCancellableStatuses, true)) {
            throw new InvalidArgumentException("Pesanan dengan status {$booking->booking_status->label()} tidak dapat dibatalkan.");
        }

        $totalPaid = (float) $booking->total_paid;
        $prevBookingStatus = $booking->booking_status->value;

        // 1. Calculate Refund Percentage
        $isPartnerOrAdmin = $actor->role === UserRole::ADMIN ||
            ($booking->partner_id && $booking->partner?->users?->contains($actor));

        if ($isPartnerOrAdmin) {
            // Partner/Admin initiated cancellation gives 100% refund of paid amount
            $refundPercentage = 100.00;
        } else {
            // Customer cancellation follows scheduled policy snapshot
            $departureDate = $booking->service_start_date->copy()->startOfDay();
            $hoursUntilDeparture = now()->diffInHours($departureDate, false);

            if ($hoursUntilDeparture > 7 * 24) { // > 168 hours
                $refundPercentage = 100.00;
            } elseif ($hoursUntilDeparture >= 3 * 24) { // 72 - 168 hours
                $refundPercentage = 50.00;
            } else { // < 72 hours
                $refundPercentage = 0.00;
            }
        }

        $refundAmount = $totalPaid > 0 ? round($totalPaid * ($refundPercentage / 100), 2) : 0.00;

        return DB::transaction(function () use (
            $booking,
            $actor,
            $reason,
            $bankInfo,
            $totalPaid,
            $refundPercentage,
            $refundAmount,
            $prevBookingStatus
        ) {
            $createdRefund = null;

            if ($refundAmount > 0) {
                $booking->booking_status = BookingStatus::REFUND_PENDING;
                $booking->payment_status = PaymentStatus::REFUND_PENDING;

                $createdRefund = Refund::create([
                    'booking_id' => $booking->id,
                    'calculated_refund_percentage' => $refundPercentage,
                    'refund_amount' => $refundAmount,
                    'reason' => $reason,
                    'bank_name' => $bankInfo['bank_name'] ?? 'BCA',
                    'bank_account_number' => $bankInfo['bank_account_number'] ?? '-',
                    'bank_account_holder' => $bankInfo['bank_account_holder'] ?? ($booking->user?->name ?? 'Pelanggan'),
                    'status' => RefundStatus::PENDING,
                ]);
            } else {
                $booking->booking_status = BookingStatus::CANCELLED;
            }

            // Always release inventory hold or allocated capacity
            $booking->inventory_status = InventoryStatus::RELEASED;
            $booking->save();

            InventoryHold::where('booking_id', $booking->id)
                ->whereIn('status', [InventoryStatus::HELD, InventoryStatus::ALLOCATED])
                ->update(['status' => InventoryStatus::RELEASED]);

            // Put any pending vendor settlement on hold
            VendorSettlement::where('booking_id', $booking->id)
                ->where('status', SettlementStatus::PENDING)
                ->update(['status' => SettlementStatus::ON_HOLD]);

            // History Log
            $formattedRefund = number_format($refundAmount, 0, ',', '.');
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'actor_id' => $actor->id,
                'actor_type' => $actor->role->value,
                'previous_status' => $prevBookingStatus,
                'new_status' => $booking->booking_status->value,
                'reason' => "Pembatalan: {$reason}. Persentase refund: {$refundPercentage}%. Nominal pengembalian dana: Rp {$formattedRefund}.",
            ]);

            // Audit Log
            AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'BOOKING_CANCELLED',
                'entity_type' => Booking::class,
                'entity_id' => $booking->id,
                'old_values' => ['booking_status' => $prevBookingStatus],
                'new_values' => [
                    'booking_status' => $booking->booking_status->value,
                    'refund_amount' => $refundAmount,
                    'refund_percentage' => $refundPercentage,
                ],
                'ip_address' => request()->ip(),
            ]);

            return [
                'booking' => $booking,
                'refund' => $createdRefund,
                'refund_amount' => $refundAmount,
                'refund_percentage' => $refundPercentage,
            ];
        });
    }
}

