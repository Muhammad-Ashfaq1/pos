<?php

namespace App\Http\Requests\Tenant\Cards;

use App\Models\Card;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $routeType = $this->route('type');

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'card_type' => is_string($routeType) && array_key_exists($routeType, Card::typeOptions())
                ? $routeType
                : $this->input('card_type'),
            'product_ids' => collect($this->input('product_ids', []))
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $cardType = $this->input('card_type');

        return [
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
            'card_type' => ['required', Rule::in(array_keys(Card::typeOptions()))],
            'name' => ['required', 'string', 'max:150'],
            'discount_type' => [
                Rule::requiredIf($cardType === Card::TYPE_DISCOUNT),
                'nullable',
                Rule::in(array_keys(Card::discountTypeOptions())),
            ],
            'value' => ['required', 'numeric', 'gt:0'],
            'minimum_spend' => ['required', 'numeric', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'valid_until' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'product_id' => ['prohibited'],
            'details' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $cardType = $this->input('card_type');
                $discountType = $this->input('discount_type');
                $value = $this->input('value');

                if (
                    $cardType === Card::TYPE_DISCOUNT
                    && $discountType === 'percentage'
                    && $value !== null
                    && (float) $value > 100
                ) {
                    $validator->errors()->add('value', 'Percentage discounts may not be greater than 100.');
                }

                if (! $this->filled('id')) {
                    return;
                }

                $existing = Card::query()->find((int) $this->input('id'));
                if ($existing && $existing->card_type !== $cardType) {
                    $validator->errors()->add('card_type', 'Card type cannot be changed.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected card was not found for this shop.',
            'card_type.required' => 'Please select a card type.',
            'card_type.in' => 'Please select a valid card type.',
            'name.required' => 'Please enter a card name.',
            'name.max' => 'The card name may not be greater than 150 characters.',
            'discount_type.required' => 'Please select a discount type.',
            'discount_type.in' => 'Please select a valid discount type.',
            'value.required' => 'Please enter a card value.',
            'value.numeric' => 'The card value must be numeric.',
            'value.gt' => 'The card value must be greater than zero.',
            'minimum_spend.required' => 'Please enter a minimum spend amount.',
            'minimum_spend.numeric' => 'The minimum spend must be numeric.',
            'minimum_spend.min' => 'The minimum spend must be zero or greater.',
            'product_ids.*.exists' => 'One or more selected products were not found for this shop.',
            'valid_until.date' => 'Please enter a valid expiry date.',
        ];
    }
}
