<?php

namespace App\Http\Requests\Tenant\Settings;

class SaveShopNotificationsSettingsRequest extends BaseShopSettingsRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'reminder_email_enabled' => $this->boolean('reminder_email_enabled'),
            'receipt_email_enabled' => $this->boolean('receipt_email_enabled'),
            'loyalty_enabled' => $this->boolean('loyalty_enabled'),
        ]);
    }

    public function rules(): array
    {
        return [
            'reminder_email_enabled' => ['required', 'boolean'],
            'receipt_email_enabled' => ['required', 'boolean'],
            'loyalty_enabled' => ['required', 'boolean'],
            'loyalty_points_per_currency' => ['required', 'numeric', 'min:0', 'max:999999'],
            'settings' => ['prohibited'],
        ];
    }
}
