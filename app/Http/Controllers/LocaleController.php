<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch active application locale (id or en).
     */
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:id,en'],
        ]);

        $locale = $validated['locale'];

        session()->put('locale', $locale);
        cookie()->queue('locale', $locale, 60 * 24 * 365); // 1 year cookie
        app()->setLocale($locale);

        return back()->with('success', $locale === 'en' ? 'Language switched to English.' : 'Bahasa dialihkan ke Bahasa Indonesia.');
    }
}
