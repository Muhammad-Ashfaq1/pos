<?php

namespace App\Http\Requests\Tenant\Settings;

use App\Models\Tenant;

class SaveShopBrandingSettingsRequest extends BaseShopSettingsRequest
{
    protected function prepareForValidation(): void
    {
        $color = strtoupper(trim((string) $this->input('primary_color', Tenant::DEFAULT_BRAND_COLOR)));

        $this->merge([
            'primary_color' => $color !== '' ? $color : Tenant::DEFAULT_BRAND_COLOR,
            'remove_logo' => filter_var($this->input('remove_logo'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:5120'],
            'remove_logo' => ['sometimes', 'boolean'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/'],
            'settings' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'Please upload a valid image for the company logo.',
            'logo.mimes' => 'Company logo must be a JPG, PNG, GIF, WebP, or SVG file.',
            'logo.max' => 'Company logo may not be larger than 5 MB.',
            'primary_color.regex' => 'Please choose a valid hex color (e.g. #20AEEB).',
        ];
    }
}
