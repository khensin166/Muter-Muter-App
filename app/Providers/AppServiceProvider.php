<?php

namespace App\Providers;

use App\Services\AppLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || (config('app.url') && str_starts_with(config('app.url'), 'https://'))) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Structured Auth Events Logging
        Event::listen(Login::class, function (Login $event) {
            AppLogger::auth('auth.login.success', "Pengguna {$event->user->name} ({$event->user->email}) berhasil masuk", [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'role' => $event->user->role instanceof \BackedEnum ? $event->user->role->value : (string) $event->user->role,
                'guard' => $event->guard,
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            AppLogger::auth('auth.login.failed', 'Percobaan login gagal untuk kredensial: '.($event->credentials['email'] ?? 'unknown'), [
                'email' => $event->credentials['email'] ?? null,
                'user_id' => $event->user?->id,
                'guard' => $event->guard,
            ], level: 'warning');
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AppLogger::auth('auth.logout', "Pengguna {$event->user->name} ({$event->user->email}) telah keluar", [
                    'user_id' => $event->user->id,
                    'email' => $event->user->email,
                    'guard' => $event->guard,
                ]);
            }
        });
    }
}
