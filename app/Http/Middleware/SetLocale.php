<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('app_locale')
            ?? $request->cookie('app_locale')
            ?? $this->defaultLocaleFromSettings()
            ?? config('app.locale', 'en');

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'en';
        }

        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        App::setLocale($locale);

        session([
            'app_locale' => $locale,
            'app_direction' => $direction,
        ]);

        return $next($request);
    }

    private function defaultLocaleFromSettings(): ?string
    {
        $tenant = $this->currentTenant();
        $locale = $tenant?->setting('regional.locale');

        return in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : null;
    }

    private function currentTenant(): ?Tenant
    {
        if (function_exists('tenant') && tenant()) {
            return tenant();
        }

        $user = auth()->user();
        if ($user?->tenant_id) {
            $user->loadMissing('tenant');

            return $user->tenant;
        }

        $customer = auth('customer')->user();
        if ($customer?->tenant_id) {
            $customer->loadMissing('tenant');

            return $customer->tenant;
        }

        return null;
    }
}
