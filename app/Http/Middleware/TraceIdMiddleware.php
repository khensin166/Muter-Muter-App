<?php

namespace App\Http\Middleware;

use App\Services\AppLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TraceIdMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Retrieve existing trace ID from reverse proxy (e.g. Caddy) or generate new UUID
        $traceId = $request->header('X-Trace-Id')
            ?: $request->header('X-Request-Id')
            ?: 'req-'.(string) Str::uuid();

        // Bind trace_id to request attributes and global logging context
        $request->attributes->set('trace_id', $traceId);
        Log::withContext(['trace_id' => $traceId]);

        $response = $next($request);

        // Attach trace_id to response header for client-side visibility
        $response->headers->set('X-Trace-Id', $traceId);

        // Record request completion log (exclude static assets and healthcheck endpoints)
        $path = $request->path();
        if (! str_starts_with($path, '_ignition') && ! str_starts_with($path, 'build') && $path !== 'up') {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            AppLogger::application(
                event: 'application.request.completed',
                message: "HTTP {$request->method()} /{$path} completed in {$durationMs}ms with status {$response->getStatusCode()}",
                context: [
                    'method' => $request->method(),
                    'endpoint' => '/'.$path,
                    'status_code' => $response->getStatusCode(),
                    'duration_ms' => $durationMs,
                ]
            );
        }

        return $response;
    }
}

