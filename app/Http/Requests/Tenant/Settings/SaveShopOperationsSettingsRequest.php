<?php

namespace App\Http\Requests\Tenant\Settings;

use Illuminate\Validation\Validator;

class SaveShopOperationsSettingsRequest extends BaseShopSettingsRequest
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
            'business_hours' => $businessHours,
        ]);
    }

    public function rules(): array
    {
        return [
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:999999'],
            'business_hours' => ['required', 'array'],
            'business_hours.*.is_closed' => ['required', 'boolean'],
            'business_hours.*.open' => ['nullable', 'date_format:H:i'],
            'business_hours.*.close' => ['nullable', 'date_format:H:i'],
            'settings' => ['prohibited'],
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
            'low_stock_threshold.min' => 'Low stock threshold must be zero or greater.',
        ];
    }
}
