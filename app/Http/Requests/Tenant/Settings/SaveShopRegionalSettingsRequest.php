<?php

namespace App\Http\Requests\Tenant\Settings;

use App\Support\Currency;
use Illuminate\Validation\Rule;

class SaveShopRegionalSettingsRequest extends BaseShopSettingsRequest
{
    /** Supported currency codes — single source of truth shared with Currency::SYMBOLS. */
    private const ALLOWED_CURRENCIES = [
        'USD', 'GBP', 'PKR', 'AED', 'SAR', 'CAD', 'AUD',
        'EUR', 'INR', 'NZD', 'JPY', 'CNY', 'BDT', 'NGN', 'ZAR',
        'BRL', 'TRY', 'RUB', 'KRW', 'CHF', 'MYR', 'SGD', 'HKD',
        'THB', 'IDR', 'PHP', 'EGP', 'QAR', 'KWD', 'OMR',
    ];

    /** Supported locale tags — must be valid BCP-47 tags present in Currency::LOCALES. */
    private const ALLOWED_LOCALES = [
        'ur-PK', 'ar-AE', 'ar-SA', 'en-US', 'en-CA', 'en-AU', 'en-GB',
        'en', 'ar', 'de-DE', 'es', 'fr',
        'en-IN', 'en-NZ', 'ja-JP', 'zh-CN', 'bn-BD', 'en-NG', 'en-ZA',
        'pt-BR', 'tr-TR', 'ru-RU', 'ko-KR', 'de-CH', 'ms-MY', 'en-SG',
        'zh-HK', 'th-TH', 'id-ID', 'fil-PH', 'ar-EG', 'ar-QA', 'ar-KW', 'ar-OM',
    ];

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency'       => strtoupper(trim((string) $this->input('currency'))),
            'timezone'       => trim((string) $this->input('timezone')),
            'locale'         => trim((string) $this->input('locale')),
            'tax_name'       => trim((string) $this->input('tax_name')),
            'invoice_prefix' => strtoupper(trim((string) $this->input('invoice_prefix'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'currency'            => ['required', 'string', Rule::in(self::ALLOWED_CURRENCIES)],
            'timezone'            => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'locale'              => ['required', 'string', Rule::in(self::ALLOWED_LOCALES)],
            'tax_name'            => ['required', 'string', 'max:100'],
            'tax_percentage'      => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix'      => ['required', 'string', 'max:20'],
            'invoice_next_number' => ['required', 'integer', 'min:1', 'max:999999999'],
            'settings'            => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency.in'             => 'Please select a supported currency.',
            'timezone.in'             => 'Please select a valid timezone.',
            'locale.in'               => 'Please select a supported locale.',
            'tax_percentage.max'      => 'Tax percentage may not be greater than 100.',
            'invoice_next_number.min' => 'Invoice next number must be at least 1.',
        ];
    }
}
