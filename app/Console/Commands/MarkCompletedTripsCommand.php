<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\SettlementStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\VendorSettlement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkCompletedTripsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'trips:mark-completed';

    /**
     * The console command description.
     */
    protected $description = 'Automatically mark finished trips (service_end_date <= today) as COMPLETED and make vendor settlements ELIGIBLE';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $completedBookings = Booking::query()
            ->where('booking_status', BookingStatus::CONFIRMED)
            ->whereDate('service_end_date', '<=', $today)
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->info('No trips to mark as completed.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($completedBookings as $booking) {
            DB::transaction(function () use ($booking, &$count) {
                $prevStatus = $booking->booking_status->value;

                $booking->booking_status = BookingStatus::COMPLETED;
                $booking->save();

                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'actor_id' => null,
                    'actor_type' => 'SYSTEM',
                    'previous_status' => $prevStatus,
                    'new_status' => BookingStatus::COMPLETED->value,
                    'reason' => 'Jadwal layanan wisata telah selesai (service_end_date tercapai). Pesanan resmi berstatus SELESAI (COMPLETED).',
                ]);

                // Transition vendor settlement to ELIGIBLE
                VendorSettlement::where('booking_id', $booking->id)
                    ->where('status', SettlementStatus::PENDING)
                    ->update([
                        'status' => SettlementStatus::ELIGIBLE,
                        'eligible_at' => now(),
                    ]);

                if ($booking->partner_id) {
                    $booking->partner()->increment('completed_trips_count');
                }

                AuditLog::create([
                    'user_id' => null,
                    'action' => 'TRIP_COMPLETED',
                    'entity_type' => Booking::class,
                    'entity_id' => $booking->id,
                    'old_values' => ['booking_status' => $prevStatus],
                    'new_values' => ['booking_status' => BookingStatus::COMPLETED->value],
                    'ip_address' => '127.0.0.1',
                ]);

                $count++;
            });
        }

        $this->info("Successfully marked {$count} trip(s) as COMPLETED.");
        Log::info("[Scheduler] Marked {$count} trips as COMPLETED and unlocked vendor settlements.");

        return self::SUCCESS;
    }
}

