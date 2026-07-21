<?php

namespace App\Http\Requests\Tenant\Cards;

use App\Http\Requests\Concerns\ValidatesCardPayload;
use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveCardRequest extends FormRequest
{
    use ValidatesCardPayload;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $routeType = $this->route('type');

        $this->prepareCardPayload([
            'card_type' => is_string($routeType) && array_key_exists($routeType, Card::typeOptions())
                ? $routeType
                : $this->input('card_type'),
        ]);
    }

    public function rules(): array
    {
        $tenantId = $this->tenantIdForCardValidation();
        $cardType = $this->input('card_type');
        $allowExistingDate = null;

        if ($this->filled('id')) {
            $existing = Card::query()->find((int) $this->input('id'));
            $allowExistingDate = $existing?->valid_until?->format('Y-m-d');
        }

        return $this->cardPayloadRules([
            'id' => [
                'nullable',
                'integer',
                Rule::exists('cards', 'id')->where(
                    fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->when(
                            is_string($cardType) && $cardType !== '',
                            fn ($q) => $q->where('card_type', $cardType)
                        )
                ),
            ],
            // Friendly edit: keep the card's current expiry even if it is already past.
            'valid_until' => $this->cardValidUntilRules([], $allowExistingDate),
            'is_active' => ['required', 'boolean'],
        ]);
    }

    public function after(): array
    {
        return [
            $this->rejectInvalidPercentageDiscount(),
            function (Validator $validator): void {
                if (! $this->filled('id')) {
                    return;
                }

                $cardType = $this->input('card_type');
                $existing = Card::query()->find((int) $this->input('id'));

                if ($existing && $existing->card_type !== $cardType) {
                    $validator->errors()->add('card_type', 'Card type cannot be changed.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return array_merge($this->cardPayloadMessages(), [
            'id.exists' => 'The selected card was not found for this shop.',
        ]);
    }
}
