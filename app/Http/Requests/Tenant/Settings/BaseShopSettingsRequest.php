<?php

namespace App\Http\Requests\Tenant\Settings;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseShopSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
