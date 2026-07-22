<?php

namespace App\Http\Requests\Employee\Cards;

use App\Http\Requests\Concerns\ValidatesCardPayload;
use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCardRequest extends FormRequest
{
    use ValidatesCardPayload;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Employee portal creates active cards only (no status toggle in the UI).
        $this->prepareCardPayload([
            'is_active' => true,
        ]);
    }

    public function rules(): array
    {
        return $this->cardPayloadRules([
            'valid_until' => $this->cardValidUntilRules([
                Rule::requiredIf($this->input('card_type') === Card::TYPE_DISCOUNT),
            ]),
            'is_active' => ['required', 'boolean'],
            'id' => ['prohibited'],
        ]);
    }

    public function after(): array
    {
        return [
            $this->rejectInvalidPercentageDiscount(),
        ];
    }

    public function messages(): array
    {
        return array_merge($this->cardPayloadMessages(), [
            'valid_until.required' => 'Please enter a valid expiry date.',
        ]);
    }
}
