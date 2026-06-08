<?php

namespace App\Support;

use App\Models\Tenant;

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
    public const DEFAULT_LOCALE = 'en-US';

    /**
     * Supported currencies: ISO code => display symbol.
     *
     * Symbols are used for quick server-side formatting. Frontend formatting
     * should use Intl.NumberFormat with the locale from LOCALES.
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
        'INR' => '₹',
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
     * ISO currency code => BCP-47 locale tag.
     * Used by JavaScript's Intl.NumberFormat for correct local number formatting.
     */
    public const LOCALES = [
        'USD' => 'en-US',
        'GBP' => 'en-GB',
        'PKR' => 'ur-PK',
        'AED' => 'ar-AE',
        'SAR' => 'ar-SA',
        'CAD' => 'en-CA',
        'AUD' => 'en-AU',
        // Extended
        'EUR' => 'de-DE',
        'INR' => 'en-IN',
        'NZD' => 'en-NZ',
        'JPY' => 'ja-JP',
        'CNY' => 'zh-CN',
        'BDT' => 'bn-BD',
        'NGN' => 'en-NG',
        'ZAR' => 'en-ZA',
        'BRL' => 'pt-BR',
        'TRY' => 'tr-TR',
        'RUB' => 'ru-RU',
        'KRW' => 'ko-KR',
        'CHF' => 'de-CH',
        'MYR' => 'ms-MY',
        'SGD' => 'en-SG',
        'HKD' => 'zh-HK',
        'THB' => 'th-TH',
        'IDR' => 'id-ID',
        'PHP' => 'fil-PH',
        'EGP' => 'ar-EG',
        'QAR' => 'ar-QA',
        'KWD' => 'ar-KW',
        'OMR' => 'ar-OM',
    ];

    /** @var array<int|string, string> per-tenant resolved symbols for the current request. */
    private static array $symbolCache = [];

    /**
     * The active ISO currency code for the given (or current) tenant.
     */
    public static function code(?Tenant $tenant = null): string
    {
        $tenant ??= self::currentTenant();
        $code = $tenant?->setting('regional.currency', self::DEFAULT_CODE);

        return is_string($code) && $code !== '' ? $code : self::DEFAULT_CODE;
    }

    /**
     * The display symbol. Pass an explicit code, or omit to use the current tenant.
     */
    public static function symbol(?string $code = null): string
    {
        if ($code !== null) {
            return self::SYMBOLS[$code] ?? self::DEFAULT_SYMBOL;
        }

        $tenant = self::currentTenant();
        $key = $tenant?->getKey() ?? 'central';

        return self::$symbolCache[$key] ??= self::symbol(self::code($tenant));
    }

    /**
     * The BCP-47 locale tag for the given (or current) currency/tenant.
     * Used by JavaScript Intl.NumberFormat for correct regional formatting.
     */
    public static function locale(?string $code = null): string
    {
        $resolvedCode = $code ?? self::code();

        return self::LOCALES[$resolvedCode] ?? self::DEFAULT_LOCALE;
    }

    /**
     * Format an amount as currency, e.g. "$1,234.50". Pass $withSymbol = false
     * for the number only.
     */
    public static function format(int|float|string|null $amount, bool $withSymbol = true): string
    {
        $value = number_format((float) $amount, 2);

        return $withSymbol ? self::symbol().$value : $value;
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
        $tenant = function_exists('tenant') ? tenant() : null;

        return $tenant instanceof Tenant ? $tenant : null;
    }
}
