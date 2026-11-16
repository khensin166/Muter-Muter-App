<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->prepend(\App\Http\Middleware\TraceIdMiddleware::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocaleMiddleware::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'partner' => \App\Http\Middleware\EnsureUserIsPartner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::warning('PostTooLargeException: Unggahan melebihi batas post_max_size server.', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'photos' => 'Ukuran total file yang diunggah terlalu besar melebihi batas server (maksimal 5 MB per foto).',
            ])->with('error', 'Ukuran total file yang diunggah terlalu besar melebihi batas server. Silakan pilih foto dengan resolusi lebih wajar atau kompres terlebih dahulu.');
        });
    })->create();
