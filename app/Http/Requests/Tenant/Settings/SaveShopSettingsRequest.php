<?php

namespace App\Http\Requests\Tenant\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class SaveShopSettingsRequest extends FormRequest
{
    private const WEEKDAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $businessHours = collect(self::WEEKDAYS)
            ->mapWithKeys(function (string $day): array {
                return [
                    $day => [
                        'is_closed' => filter_var($this->input("business_hours.{$day}.is_closed", false), FILTER_VALIDATE_BOOL),
                        'open' => $this->normalizeNullableString($this->input("business_hours.{$day}.open")),
                        'close' => $this->normalizeNullableString($this->input("business_hours.{$day}.close")),
                    ],
                ];
            })
            ->all();

        $this->merge([
            'shop_name' => trim((string) $this->input('shop_name')),
            'business_name' => trim((string) $this->input('business_name')),
            'owner_name' => trim((string) $this->input('owner_name')),
            'business_email' => $this->normalizeNullableString(mb_strtolower((string) $this->input('business_email'))),
            'business_phone' => $this->normalizeNullableString($this->input('business_phone')),
            'website_url' => $this->normalizeNullableString($this->input('website_url')),
            'address' => $this->normalizeNullableString($this->input('address')),
            'city' => $this->normalizeNullableString($this->input('city')),
            'state' => $this->normalizeNullableString($this->input('state')),
            'country' => $this->normalizeNullableString($this->input('country')),
            'currency' => strtoupper(trim((string) $this->input('currency'))),
            'timezone' => trim((string) $this->input('timezone')),
            'locale' => trim((string) $this->input('locale')),
            'tax_name' => trim((string) $this->input('tax_name')),
            'invoice_prefix' => strtoupper(trim((string) $this->input('invoice_prefix'))),
            'reminder_email_enabled' => $this->boolean('reminder_email_enabled'),
            'receipt_email_enabled' => $this->boolean('receipt_email_enabled'),
            'loyalty_enabled' => $this->boolean('loyalty_enabled'),
            'business_hours' => $businessHours,
            'active_tab' => trim((string) $this->input('active_tab', 'general')),
        ]);
    }

    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:150'],
            'business_name' => ['required', 'string', 'max:150'],
            'owner_name' => ['required', 'string', 'max:150'],
            'business_email' => ['nullable', 'email', 'max:150'],
            'business_phone' => ['nullable', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:150'],
            'state' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:150'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'locale' => ['required', 'string', 'max:10'],
            'tax_name' => ['required', 'string', 'max:100'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'invoice_next_number' => ['required', 'integer', 'min:1', 'max:999999999'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:999999'],
            'reminder_email_enabled' => ['required', 'boolean'],
            'receipt_email_enabled' => ['required', 'boolean'],
            'loyalty_enabled' => ['required', 'boolean'],
            'loyalty_points_per_currency' => ['required', 'numeric', 'min:0', 'max:999999'],
            'active_tab' => ['nullable', 'string', Rule::in(['general', 'regional', 'operations', 'notifications'])],
            'business_hours' => ['required', 'array'],
            'business_hours.*.is_closed' => ['required', 'boolean'],
            'business_hours.*.open' => ['nullable', 'date_format:H:i'],
            'business_hours.*.close' => ['nullable', 'date_format:H:i'],
            'settings' => ['prohibited'],
            'onboarding_status' => ['prohibited'],
            'onboarding_completed_at' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'approved_by' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (self::WEEKDAYS as $day) {
                if ($this->boolean("business_hours.{$day}.is_closed")) {
                    continue;
                }

                $open = $this->input("business_hours.{$day}.open");
                $close = $this->input("business_hours.{$day}.close");

                if (! $open || ! $close) {
                    $validator->errors()->add("business_hours.{$day}.open", 'Open and close times are required for active business days.');
                    continue;
                }

                if ($open >= $close) {
                    $validator->errors()->add("business_hours.{$day}.close", 'Close time must be later than open time.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'shop_name.required' => 'Please enter the shop name.',
            'business_name.required' => 'Please enter the business name.',
            'owner_name.required' => 'Please enter the primary contact name.',
            'business_email.email' => 'Please enter a valid business email address.',
            'website_url.url' => 'Please enter a valid website URL.',
            'currency.size' => 'Currency must be a 3-letter code.',
            'timezone.in' => 'Please select a valid timezone.',
            'tax_percentage.max' => 'Tax percentage may not be greater than 100.',
            'invoice_next_number.min' => 'Invoice next number must be at least 1.',
            'low_stock_threshold.min' => 'Low stock threshold must be zero or greater.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
