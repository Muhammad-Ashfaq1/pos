<?php

namespace App\Http\Requests\Tenant\Services;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mappings' => array_values($this->input('mappings', [])),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $serviceId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('services', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($serviceId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('services', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($serviceId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'standard_price' => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reminder_interval_days' => ['nullable', 'integer', 'min:0'],
            'mileage_interval' => ['nullable', 'integer', 'min:0'],
            'requires_technician' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'mappings' => ['nullable', 'array'],
            'mappings.*.product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'mappings.*.quantity' => ['nullable', 'numeric', 'min:0.001', 'gte:0.001'],
            'mappings.*.unit' => ['nullable', 'string', 'max:50'],
            'mappings.*.is_required' => ['required', 'boolean'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $mappings = collect($this->input('mappings', []));
            $seenProductIds = [];

            $mappings->each(function (array $mapping, int $index) use (&$seenProductIds, $validator): void {
                $productId = (int) ($mapping['product_id'] ?? 0);
                $quantity = $mapping['quantity'] ?? null;
                $unit = trim((string) ($mapping['unit'] ?? ''));
                $hasValue = $productId > 0 || $quantity !== null && $quantity !== '' || $unit !== '';

                if (! $hasValue) {
                    return;
                }

                if ($productId <= 0) {
                    $validator->errors()->add("mappings.{$index}.product_id", 'Please select a product.');
                }

                if ($quantity === null || $quantity === '') {
                    $validator->errors()->add("mappings.{$index}.quantity", 'Please enter a quantity.');
                }

                if ($productId > 0) {
                    if (in_array($productId, $seenProductIds, true)) {
                        $validator->errors()->add("mappings.{$index}.product_id", 'Each product can only be added once per service.');
                    }

                    $seenProductIds[] = $productId;
                }
            });
        });
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected service was not found for this shop.',
            'category_id.exists' => 'The selected category was not found for this shop.',
            'name.required' => 'Please enter a service name.',
            'name.max' => 'The service name may not be greater than 150 characters.',
            'name.unique' => 'This service name already exists for this shop.',
            'code.max' => 'The service code may not be greater than 50 characters.',
            'code.unique' => 'This service code already exists for this shop.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'standard_price.required' => 'Please enter a standard price.',
            'standard_price.numeric' => 'The standard price must be numeric.',
            'standard_price.min' => 'The standard price must be zero or greater.',
            'estimated_duration_minutes.integer' => 'Estimated duration must be a whole number.',
            'estimated_duration_minutes.min' => 'Estimated duration must be zero or greater.',
            'tax_percentage.numeric' => 'Tax percentage must be numeric.',
            'tax_percentage.min' => 'Tax percentage must be zero or greater.',
            'tax_percentage.max' => 'Tax percentage may not be greater than 100.',
            'reminder_interval_days.integer' => 'Reminder interval must be a whole number.',
            'reminder_interval_days.min' => 'Reminder interval must be zero or greater.',
            'mileage_interval.integer' => 'Mileage interval must be a whole number.',
            'mileage_interval.min' => 'Mileage interval must be zero or greater.',
            'mappings.*.product_id.exists' => 'The selected product was not found for this shop.',
            'mappings.*.quantity.numeric' => 'Mapped quantity must be numeric.',
            'mappings.*.quantity.min' => 'Mapped quantity must be greater than zero.',
            'mappings.*.unit.max' => 'The mapping unit may not be greater than 50 characters.',
        ];
    }
}
