<?php

namespace App\Http\Requests\Concerns;

use App\Models\Card;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesCardPayload
{
    protected function tenantIdForCardValidation(): int|string|null
    {
        return app(TenantContext::class)->id();
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function prepareCardPayload(array $extra = []): void
    {
        $this->merge(array_merge([
            'name' => trim((string) $this->input('name')),
            'product_ids' => collect($this->input('product_ids', []))
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
        ], $extra));
    }

    /**
     * Shared create/update field rules. Portal requests add their own extras
     * (e.g. id, valid_until constraints, is_active defaults).
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function cardPayloadRules(array $extra = []): array
    {
        $tenantId = $this->tenantIdForCardValidation();
        $cardType = $this->input('card_type');

        return array_merge([
            'card_type' => ['required', Rule::in(array_keys(Card::typeOptions()))],
            'name' => ['required', 'string', 'max:150'],
            'discount_type' => [
                Rule::requiredIf($cardType === Card::TYPE_DISCOUNT),
                'nullable',
                Rule::in(array_keys(Card::discountTypeOptions())),
            ],
            // Reward cards may be created without a points value (defaults to 0);
            // discount/gift cards always require a value greater than zero.
            'value' => $cardType === Card::TYPE_REWARD
                ? ['nullable', 'numeric', 'min:0']
                : ['required', 'numeric', 'gt:0'],
            'minimum_spend' => ['required', 'numeric', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'product_id' => ['prohibited'],
            'details' => ['prohibited'],
        ], $extra);
    }

    /**
     * Expiry rule: today or future by default.
     * Admin edit may keep the card's existing past date (friendly update flow).
     *
     * @param  list<string|\Illuminate\Contracts\Validation\ValidationRule>  $extra
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule|\Closure>
     */
    protected function cardValidUntilRules(array $extra = [], ?string $allowExistingDate = null): array
    {
        $rules = array_values(array_merge($extra, [
            'nullable',
            'date',
        ]));

        if ($allowExistingDate === null || $allowExistingDate === '') {
            $rules[] = 'after_or_equal:today';

            return $rules;
        }

        $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($allowExistingDate): void {
            if ($value === null || $value === '') {
                return;
            }

            try {
                $selected = \Illuminate\Support\Carbon::parse((string) $value)->startOfDay();
                $existing = \Illuminate\Support\Carbon::parse($allowExistingDate)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if ($selected->equalTo($existing) || $selected->greaterThanOrEqualTo(today())) {
                return;
            }

            $fail('The expiry date must be today or a future date.');
        };

        return $rules;
    }

    protected function rejectInvalidPercentageDiscount(): Closure
    {
        return function (Validator $validator): void {
            $cardType = $this->input('card_type');
            $discountType = $this->input('discount_type');
            $value = $this->input('value');

            if (
                $cardType === Card::TYPE_DISCOUNT
                && $discountType === 'percentage'
                && $value !== null
                && (float) $value > 100
            ) {
                $validator->errors()->add(
                    'value',
                    'Percentage discounts may not be greater than 100.'
                );
            }
        };
    }

    /**
     * @return array<string, string>
     */
    protected function cardPayloadMessages(): array
    {
        return [
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
            'valid_until.after_or_equal' => 'The expiry date must be today or a future date.',
        ];
    }
}
