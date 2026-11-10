<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'UNPAID';
    case PARTIALLY_PAID = 'PARTIALLY_PAID';
    case PAYMENT_REVIEW = 'PAYMENT_REVIEW';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
    case FAILED = 'FAILED';
    case REFUND_PENDING = 'REFUND_PENDING';
    case REFUNDED = 'REFUNDED';

    public function label(): string
    {
        if (app()->getLocale() === 'en') {
            return match ($this) {
                self::UNPAID => 'Unpaid',
                self::PARTIALLY_PAID => 'DP Verified (Partially Paid)',
                self::PAYMENT_REVIEW => 'Awaiting Admin Verification',
                self::PAID => 'Paid in Full',
                self::OVERDUE => 'Payment Overdue',
                self::FAILED => 'Payment Failed',
                self::REFUND_PENDING => 'Refund In Progress',
                self::REFUNDED => 'Refunded',
            };
        }

        return match ($this) {
            self::UNPAID => 'Belum Dibayar',
            self::PARTIALLY_PAID => 'DP Terverifikasi (Belum Lunas)',
            self::PAYMENT_REVIEW => 'Menunggu Verifikasi Admin',
            self::PAID => 'Lunas',
            self::OVERDUE => 'Terlambat Bayar',
            self::FAILED => 'Pembayaran Gagal',
            self::REFUND_PENDING => 'Proses Pengembalian Dana',
            self::REFUNDED => 'Dana Dikembalikan',
        };
    }
}

