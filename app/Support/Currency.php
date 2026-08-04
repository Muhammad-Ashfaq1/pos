<?php

namespace App\Support;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;

/**
 * Central currency resolver/formatter. The active currency is read from the
 * current tenant's `regional.currency` setting and falls back to USD ($) when
 * no tenant is in context (central/super-admin) or the setting is unset.
 *
 * Use everywhere a money value is rendered so the whole app honours the shop's
 * configured currency instead of a hardcoded symbol.
 */
class Currency
{
    public const DEFAULT_CODE   = 'USD';
    public const DEFAULT_SYMBOL = '$';

    /**
     * ISO currency code => display symbol.
     *
     * Symbols are used for quick server-side formatting.
     */
    public const SYMBOLS = [
        'USD' => '$',
        'GBP' => '£',
        'PKR' => '₨',
        'AED' => 'د.إ',
        'SAR' => '﷼',
        'CAD' => 'C$',
        'AUD' => 'A$',
        // Extended set (not in primary list but kept for compatibility)
        'EUR' => '€',
        'NZD' => 'NZ$',
        'JPY' => '¥',
        'CNY' => '¥',
        'BDT' => '৳',
        'NGN' => '₦',
        'ZAR' => 'R',
        'BRL' => 'R$',
        'TRY' => '₺',
        'RUB' => '₽',
        'KRW' => '₩',
        'CHF' => 'CHF ',
        'MYR' => 'RM',
        'SGD' => 'S$',
        'HKD' => 'HK$',
        'THB' => '฿',
        'IDR' => 'Rp',
        'PHP' => '₱',
        'EGP' => 'E£',
        'QAR' => 'ر.ق',
        'KWD' => 'د.ك',
        'OMR' => 'ر.ع.',
    ];

    /** @var array<int|string, string> per-tenant resolved symbols for the current request. */
    private static array $symbolCache = [];

    /** Temporary tenant override (PDF/mail/jobs that render outside request tenancy). */
    private static ?Tenant $overrideTenant = null;

    /**
     * The active ISO currency code for the given (or current) tenant.
     */
    public static function code(?Tenant $tenant = null): string
    {
        $tenant ??= self::currentTenant();
        $code = $tenant?->setting('regional.currency', self::DEFAULT_CODE);

        return is_string($code) && $code !== '' ? strtoupper($code) : self::DEFAULT_CODE;
    }

    /**
     * The display symbol. Pass an explicit code, or omit to use the current tenant.
     * When regional currency is unset / unknown, defaults to `$`.
     */
    public static function symbol(?string $code = null, ?Tenant $tenant = null): string
    {
        if ($code !== null) {
            $normalized = strtoupper($code);

            return self::SYMBOLS[$normalized] ?? self::DEFAULT_SYMBOL;
        }

        $tenant ??= self::currentTenant();
        $key = $tenant?->getKey() ?? 'central';

        return self::$symbolCache[$key] ??= self::symbol(self::code($tenant));
    }

    /**
     * Format an amount as currency, e.g. "$1,234.50". Pass $withSymbol = false
     * for the number only. Optionally pass a tenant when tenancy is not active
     * (queued mail, PDF for a specific shop, etc.).
     */
    public static function format(int|float|string|null $amount, bool $withSymbol = true, ?Tenant $tenant = null): string
    {
        $value = number_format((float) $amount, 2);

        if (! $withSymbol) {
            return $value;
        }

        return self::symbol(null, $tenant).$value;
    }

    /**
     * Run a callback with an explicit tenant for currency resolution.
     * Useful when rendering PDFs/emails for an order's shop.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function using(?Tenant $tenant, Closure $callback): mixed
    {
        $previous = self::$overrideTenant;
        self::$overrideTenant = $tenant;
        self::flushCache();

        try {
            return $callback();
        } finally {
            self::$overrideTenant = $previous;
            self::flushCache();
        }
    }

    /**
     * Clear the per-request symbol cache (useful in seeders/tests that switch tenants).
     */
    public static function flushCache(): void
    {
        self::$symbolCache = [];
    }

    private static function currentTenant(): ?Tenant
    {
        if (self::$overrideTenant instanceof Tenant) {
            return self::$overrideTenant;
        }

        $tenant = function_exists('tenant') ? tenant() : null;
        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        if (app()->bound(TenantContext::class)) {
            $fromContext = app(TenantContext::class)->current();
            if ($fromContext instanceof Tenant) {
                return $fromContext;
            }
        }

        return null;
    }
}
