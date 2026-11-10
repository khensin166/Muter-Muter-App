<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING_APPROVAL = 'PENDING_APPROVAL';
    case MANUAL_REVIEW = 'MANUAL_REVIEW';
    case AWAITING_PAYMENT = 'AWAITING_PAYMENT';
    case PAYMENT_REVIEW = 'PAYMENT_REVIEW';
    case CONFIRMED = 'CONFIRMED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case EXPIRED = 'EXPIRED';
    case REFUND_PENDING = 'REFUND_PENDING';
    case REFUNDED = 'REFUNDED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        if (app()->getLocale() === 'en') {
            return match ($this) {
                self::PENDING_APPROVAL => 'Awaiting Partner Approval',
                self::MANUAL_REVIEW => 'Admin Manual Review',
                self::AWAITING_PAYMENT => 'Awaiting DP Payment',
                self::PAYMENT_REVIEW => 'Payment Verification',
                self::CONFIRMED => 'Confirmed',
                self::IN_PROGRESS => 'In Progress',
                self::COMPLETED => 'Completed',
                self::CANCELLED => 'Cancelled',
                self::EXPIRED => 'Expired',
                self::REFUND_PENDING => 'Refund Pending',
                self::REFUNDED => 'Refunded',
                self::REJECTED => 'Rejected',
            };
        }

        return match ($this) {
            self::PENDING_APPROVAL => 'Menunggu Persetujuan Mitra',
            self::MANUAL_REVIEW => 'Intervensi Manual Admin',
            self::AWAITING_PAYMENT => 'Menunggu Pembayaran DP',
            self::PAYMENT_REVIEW => 'Verifikasi Pembayaran',
            self::CONFIRMED => 'Terkonfirmasi',
            self::IN_PROGRESS => 'Sedang Berlangsung',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Kedaluwarsa',
            self::REFUND_PENDING => 'Menunggu Pengembalian Dana',
            self::REFUNDED => 'Dana Dikembalikan',
            self::REJECTED => 'Ditolak',
        };
    }
}

