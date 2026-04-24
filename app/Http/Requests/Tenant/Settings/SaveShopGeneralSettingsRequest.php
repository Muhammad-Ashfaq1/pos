<?php

namespace App\Http\Requests\Tenant\Settings;

class SaveShopGeneralSettingsRequest extends BaseShopSettingsRequest
{
    protected function prepareForValidation(): void
    {
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
            'settings' => ['prohibited'],
            'onboarding_status' => ['prohibited'],
            'onboarding_completed_at' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'approved_by' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.required' => 'Please enter the shop name.',
            'business_name.required' => 'Please enter the business name.',
            'owner_name.required' => 'Please enter the primary contact name.',
            'business_email.email' => 'Please enter a valid business email address.',
            'website_url.url' => 'Please enter a valid website URL.',
        ];
    }
}
