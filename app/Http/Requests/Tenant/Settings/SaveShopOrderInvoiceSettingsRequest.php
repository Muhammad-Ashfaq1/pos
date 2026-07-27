<?php

namespace App\Http\Requests\Tenant\Settings;

class SaveShopOrderInvoiceSettingsRequest extends BaseShopSettingsRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicle_required' => $this->boolean('vehicle_required'),
        ]);
    }

    public function rules(): array
    {
        return [
            'vehicle_required' => ['required', 'boolean'],
            'return_days_after_purchase' => ['required', 'integer', 'min:0', 'max:3650'],
            'credit_min_redeem_balance' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'settings' => ['prohibited'],
        ];
    }
}
