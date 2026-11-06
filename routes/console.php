<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Check for 24-hour partner SLA timeouts every 15 minutes
Schedule::command('bookings:check-sla-timeouts')->everyFifteenMinutes();

// Release expired 24-hour temporary inventory holds every 15 minutes
Schedule::command('inventory:release-expired-holds')->everyFifteenMinutes();

// Automatically mark completed trips and unlock vendor settlements daily
Schedule::command('trips:mark-completed')->daily();
