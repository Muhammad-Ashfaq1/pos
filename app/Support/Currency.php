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
 *
 * Web UI uses {@see SYMBOLS} (may include Arabic / specialty glyphs).
 * DomPDF / other Latin-only renderers must use {@see formatPdf()} / {@see pdfSymbol()}.
 */
class Currency
{
    public const DEFAULT_CODE   = 'USD';
    public const DEFAULT_SYMBOL = '$';

    /**
     * ISO code => short display name (settings dropdown, etc.).
     */
    public const LABELS = [
        'USD' => 'US Dollar',
        'GBP' => 'British Pound',
        'PKR' => 'Pakistani Rupee',
        'AED' => 'UAE Dirham',
        'SAR' => 'Saudi Riyal',
        'CAD' => 'Canadian Dollar',
        'AUD' => 'Australian Dollar',
        'EUR' => 'Euro',
        'NZD' => 'New Zealand Dollar',
        'JPY' => 'Japanese Yen',
        'CNY' => 'Chinese Yuan',
        'BDT' => 'Bangladeshi Taka',
        'NGN' => 'Nigerian Naira',
        'ZAR' => 'South African Rand',
        'BRL' => 'Brazilian Real',
        'TRY' => 'Turkish Lira',
        'RUB' => 'Russian Ruble',
        'KRW' => 'South Korean Won',
        'CHF' => 'Swiss Franc',
        'MYR' => 'Malaysian Ringgit',
        'SGD' => 'Singapore Dollar',
        'HKD' => 'Hong Kong Dollar',
        'THB' => 'Thai Baht',
        'IDR' => 'Indonesian Rupiah',
        'PHP' => 'Philippine Peso',
        'EGP' => 'Egyptian Pound',
        'QAR' => 'Qatari Riyal',
        'KWD' => 'Kuwaiti Dinar',
        'OMR' => 'Omani Rial',
    ];

    /**
     * ISO currency code => web UI display symbol.
     */
    public const SYMBOLS = [
        'USD' => '$',
        'GBP' => '£',
        'PKR' => '₨',
        'AED' => 'د.إ',
        'SAR' => '﷼',
        'CAD' => 'C$',
        'AUD' => 'A$',
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

    /**
     * Latin-safe symbols for DomPDF (Helvetica / limited Unicode).
     * Every key in SYMBOLS must have an entry here.
     */
    public const PDF_SYMBOLS = [
        'USD' => '$',
        'GBP' => 'GBP ',
        'PKR' => 'Rs ',
        'AED' => 'AED ',
        'SAR' => 'SAR ',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'EUR' => 'EUR ',
        'NZD' => 'NZ$',
        'JPY' => 'JPY ',
        'CNY' => 'CNY ',
        'BDT' => 'Tk ',
        'NGN' => 'NGN ',
        'ZAR' => 'R',
        'BRL' => 'R$',
        'TRY' => 'TRY ',
        'RUB' => 'RUB ',
        'KRW' => 'KRW ',
        'CHF' => 'CHF ',
        'MYR' => 'RM',
        'SGD' => 'S$',
        'HKD' => 'HK$',
        'THB' => 'THB ',
        'IDR' => 'Rp',
        'PHP' => 'PHP ',
        'EGP' => 'EGP ',
        'QAR' => 'QAR ',
        'KWD' => 'KWD ',
        'OMR' => 'OMR ',
    ];

    /** @var array<int|string, string> per-tenant resolved symbols for the current request. */
    private static array $symbolCache = [];

    /** @var array<int|string, string> per-tenant PDF-safe symbols for the current request. */
    private static array $pdfSymbolCache = [];

    /** Temporary tenant override (PDF/mail/jobs that render outside request tenancy). */
    private static ?Tenant $overrideTenant = null;

    /**
     * All supported ISO currency codes.
     *
     * @return list<string>
     */
    public static function allowedCodes(): array
    {
        return array_keys(self::SYMBOLS);
    }

    /**
     * Settings dropdown options: code => "Label (CODE)  symbol".
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::SYMBOLS as $code => $symbol) {
            $label = self::LABELS[$code] ?? $code;
            $options[$code] = "{$label} ({$code})  {$symbol}";
        }

        return $options;
    }

    /**
     * The active ISO currency code for the given (or current) tenant.
     */
    public static function code(?Tenant $tenant = null): string
    {
        $tenant ??= self::currentTenant();
        $code = $tenant?->setting('regional.currency', self::DEFAULT_CODE);

        if (! is_string($code) || $code === '') {
            return self::DEFAULT_CODE;
        }

        $normalized = strtoupper($code);

        return isset(self::SYMBOLS[$normalized]) ? $normalized : self::DEFAULT_CODE;
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
     * PDF-safe currency symbol (ASCII / Latin). Prefer this in DomPDF views.
     */
    public static function pdfSymbol(?string $code = null, ?Tenant $tenant = null): string
    {
        if ($code !== null) {
            $normalized = strtoupper($code);

            if (isset(self::PDF_SYMBOLS[$normalized])) {
                return self::PDF_SYMBOLS[$normalized];
            }

            $webSymbol = self::SYMBOLS[$normalized] ?? null;
            if (is_string($webSymbol) && ! preg_match('/[^\x20-\x7E]/u', $webSymbol)) {
                return $webSymbol;
            }

            return ($normalized !== '' ? $normalized : self::DEFAULT_CODE).' ';
        }

        $tenant ??= self::currentTenant();
        $key = $tenant?->getKey() ?? 'central';

        return self::$pdfSymbolCache[$key] ??= self::pdfSymbol(self::code($tenant));
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
     * Format money for PDF (and other Latin-only) output, e.g. "AED 41.99".
     */
    public static function formatPdf(int|float|string|null $amount, ?Tenant $tenant = null): string
    {
        return self::pdfSymbol(null, $tenant).number_format((float) $amount, 2);
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
        self::$pdfSymbolCache = [];
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
