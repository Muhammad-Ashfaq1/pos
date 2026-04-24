<?php

namespace App\Http\Requests\Tenant\Settings;

use Illuminate\Validation\Rule;

class SaveShopRegionalSettingsRequest extends BaseShopSettingsRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => strtoupper(trim((string) $this->input('currency'))),
            'timezone' => trim((string) $this->input('timezone')),
            'locale' => trim((string) $this->input('locale')),
            'tax_name' => trim((string) $this->input('tax_name')),
            'invoice_prefix' => strtoupper(trim((string) $this->input('invoice_prefix'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'locale' => ['required', 'string', 'max:10'],
            'tax_name' => ['required', 'string', 'max:100'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'invoice_next_number' => ['required', 'integer', 'min:1', 'max:999999999'],
            'settings' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency.size' => 'Currency must be a 3-letter code.',
            'timezone.in' => 'Please select a valid timezone.',
            'tax_percentage.max' => 'Tax percentage may not be greater than 100.',
            'invoice_next_number.min' => 'Invoice next number must be at least 1.',
        ];
    }
}
