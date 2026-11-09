<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role instanceof \BackedEnum ? $user->role->value : ($user->role ?? 'CUSTOMER'),
                    'partner' => $user->isPartner() ? $user->getPartnerData() : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => fn () => app()->getLocale(),
            'translations' => function () {
                $locale = app()->getLocale();
                $fallbackPath = lang_path('id.json');
                $localePath = lang_path("{$locale}.json");

                $fallback = is_file($fallbackPath) ? (json_decode(file_get_contents($fallbackPath), true) ?: []) : [];
                $current = ($locale !== 'id' && is_file($localePath)) ? (json_decode(file_get_contents($localePath), true) ?: []) : [];

                return array_replace($fallback, $current);
            },
            'trace_id' => fn () => $request->attributes->get('trace_id'),
        ];
    }
}
