<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPartner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isPartner()) {
            abort(403, 'Akses terbatas untuk Mitra Vendor terdaftar.');
        }

        $partner = $request->user()->currentPartner();
        if (! $partner) {
            abort(403, 'Anda belum terhubung dengan profil usaha mitra manapun.');
        }

        return $next($request);
    }
}

