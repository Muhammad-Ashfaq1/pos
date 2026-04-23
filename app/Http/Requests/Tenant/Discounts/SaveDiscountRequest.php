<?php

namespace App\Http\Requests\Tenant\Discounts;

use App\Models\Discount;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'code' => $this->normalizeNullableUpperString($this->input('code')),
            'description' => $this->normalizeNullableString($this->input('description')),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $discountId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('discounts', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('discounts', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($discountId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_type' => ['required', Rule::in(array_keys(Discount::typeOptions()))],
            'applies_to' => ['required', Rule::in(array_keys(Discount::appliesToOptions()))],
            'value' => ['required', 'numeric', 'gt:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
            'is_combinable' => ['required', 'boolean'],
            'requires_reason' => ['required', 'boolean'],
            'requires_manager_approval' => ['required', 'boolean'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $discountType = $this->input('discount_type');
                $value = $this->input('value');

                if ($discountType === Discount::TYPE_PERCENTAGE && $value !== null && (float) $value > 100) {
                    $validator->errors()->add('value', 'Percentage discounts may not be greater than 100.');
                }

                if (
                    $this->input('applies_to') === Discount::APPLIES_TO_CUSTOMER_PROFILE
                    && $this->boolean('requires_manager_approval')
                ) {
                    $validator->errors()->add('requires_manager_approval', 'Customer profile discounts should not require manager approval by default.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected discount was not found for this shop.',
            'name.required' => 'Please enter a discount name.',
            'name.max' => 'The discount name may not be greater than 150 characters.',
            'code.max' => 'The code may not be greater than 50 characters.',
            'code.unique' => 'This code already exists for this shop.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'discount_type.required' => 'Please select a discount type.',
            'discount_type.in' => 'Please select a valid discount type.',
            'applies_to.required' => 'Please select where this discount applies.',
            'applies_to.in' => 'Please select a valid discount target.',
            'value.required' => 'Please enter a discount value.',
            'value.numeric' => 'The discount value must be numeric.',
            'value.gt' => 'The discount value must be greater than zero.',
            'max_discount_amount.numeric' => 'The maximum discount amount must be numeric.',
            'max_discount_amount.min' => 'The maximum discount amount must be zero or greater.',
            'ends_at.after_or_equal' => 'The end date must be after or equal to the start date.',
            'usage_limit.integer' => 'The usage limit must be a whole number.',
            'usage_limit.min' => 'The usage limit must be at least 1.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeNullableUpperString(mixed $value): ?string
    {
        $value = $this->normalizeNullableString($value);

        return $value !== null ? strtoupper($value) : null;
    }
}
