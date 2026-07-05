<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    private const SUPPORTED_LOCALES = ['en', 'ar'];

    public function switch(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, self::SUPPORTED_LOCALES, true), 404);

        session([
            'app_locale' => $locale,
            'app_direction' => $locale === 'ar' ? 'rtl' : 'ltr',
        ]);

        return redirect()
            ->back(fallback: route('home'))
            ->withCookie(cookie('app_locale', $locale, 60 * 24 * 365));
    }
}
