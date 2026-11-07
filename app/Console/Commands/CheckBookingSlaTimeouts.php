<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Services\AppLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckBookingSlaTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:check-sla-timeouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa pesanan yang melebihi batas waktu SLA mitra (24 jam) dan alihkan ke intervensi manual Admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredBookings = Booking::query()
            ->where('booking_status', BookingStatus::PENDING_APPROVAL)
            ->whereNotNull('vendor_sla_deadline')
            ->where('vendor_sla_deadline', '<', now())
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('Tidak ada pesanan yang melanggar SLA 24 jam.');

            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$expiredBookings->count()} pesanan yang melanggar batas SLA mitra 24 jam. Mengalihkan ke MANUAL_REVIEW...");

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking) {
                $prevStatus = $booking->booking_status->value;

                $booking->booking_status = BookingStatus::MANUAL_REVIEW;
                $booking->save();

                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'actor_id' => null,
                    'actor_type' => 'SYSTEM',
                    'previous_status' => $prevStatus,
                    'new_status' => BookingStatus::MANUAL_REVIEW->value,
                    'reason' => 'Mitra tidak merespons ketersediaan dalam batas waktu SLA 24 jam. Dialihkan otomatis ke antrean intervensi manual Admin.',
                    'metadata' => [
                        'sla_deadline' => $booking->vendor_sla_deadline?->toIso8601String(),
                        'escalated_at' => now()->toIso8601String(),
                    ],
                ]);
            });

            AppLogger::booking('booking.sla.breached', "SLA 24 jam terlampaui untuk booking [{$booking->booking_code}], dialihkan ke MANUAL_REVIEW", [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'partner_id' => $booking->partner_id,
                'sla_deadline' => $booking->vendor_sla_deadline?->toIso8601String(),
            ], level: 'warning');

            $this->line("Pesanan [{$booking->booking_code}] berhasil dialihkan ke MANUAL_REVIEW.");
        }

        $this->info('Eskalasi SLA timeout selesai.');

        return self::SUCCESS;
    }
}

