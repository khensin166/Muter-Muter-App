<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\InventoryStatus;
use App\Enums\PaymentStatus;
use App\Models\BookingStatusHistory;
use App\Models\InventoryHold;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredInventoryHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:release-expired-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lepas temporary inventory hold 24 jam yang belum dibayar customer dan ubah status pesanan menjadi EXPIRED';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredHolds = InventoryHold::query()
            ->where('status', InventoryStatus::HELD)
            ->where('expires_at', '<', now())
            ->with('booking')
            ->get();

        if ($expiredHolds->isEmpty()) {
            $this->info('Tidak ada inventory hold yang kedaluwarsa.');

            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$expiredHolds->count()} inventory hold kedaluwarsa. Melepaskan ketersediaan...");

        foreach ($expiredHolds as $hold) {
            DB::transaction(function () use ($hold) {
                $hold->status = InventoryStatus::RELEASED;
                $hold->save();

                $booking = $hold->booking;

                if ($booking && in_array($booking->booking_status, [BookingStatus::AWAITING_PAYMENT, BookingStatus::PENDING_APPROVAL])) {
                    $prevStatus = $booking->booking_status->value;

                    $booking->booking_status = BookingStatus::EXPIRED;
                    $booking->payment_status = PaymentStatus::OVERDUE;
                    $booking->inventory_status = InventoryStatus::RELEASED;
                    $booking->save();

                    BookingStatusHistory::create([
                        'booking_id' => $booking->id,
                        'actor_id' => null,
                        'actor_type' => 'SYSTEM',
                        'previous_status' => $prevStatus,
                        'new_status' => BookingStatus::EXPIRED->value,
                        'reason' => 'Batas waktu pembayaran DP (24 jam) telah habis tanpa transfer dana. Hold ketersediaan dilepas kembali ke publik dan pesanan kedaluwarsa (EXPIRED).',
                        'metadata' => [
                            'hold_id' => $hold->id,
                            'expired_at' => now()->toIso8601String(),
                        ],
                    ]);

                    $this->line("Pesanan [{$booking->booking_code}] diubah ke EXPIRED dan ketersediaan dilepas.");
                }
            });
        }

        $this->info('Pelepasan inventory hold selesai.');

        return self::SUCCESS;
    }
}

