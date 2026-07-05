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
            'title_en' => trim((string) ($this->input('title_en') ?: $this->input('name'))),
            'title_ar' => trim((string) $this->input('title_ar')),
            'description_en' => $this->input('description_en', $this->input('description')),
            'description_ar' => $this->input('description_ar'),
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
            'title_en' => [
                'required',
                'string',
                'max:150',
                Rule::unique('services', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($serviceId),
            ],
            'title_ar' => ['nullable', 'string', 'max:150'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('services', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($serviceId),
            ],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
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
            'mappings.*.quantity' => ['nullable', 'integer', 'min:1'],
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
                    $validator->errors()->add("mappings.{$index}.product_id", __('services.select_product_error'));
                }

                if ($quantity === null || $quantity === '') {
                    $validator->errors()->add("mappings.{$index}.quantity", __('services.quantity_error'));
                }

                if ($productId > 0) {
                    if (in_array($productId, $seenProductIds, true)) {
                        $validator->errors()->add("mappings.{$index}.product_id", __('services.duplicate_product_error'));
                    }

                    $seenProductIds[] = $productId;
                }
            });
        });
    }

    public function messages(): array
    {
        return [
            'id.exists' => __('services.selected_service_missing'),
            'category_id.exists' => __('services.selected_category_missing'),
            'title_en.required' => __('services.title_en_required'),
            'title_en.max' => __('services.title_en_max'),
            'title_en.unique' => __('services.title_unique'),
            'title_ar.max' => __('services.title_ar_max'),
            'code.max' => __('services.code_max'),
            'code.unique' => __('services.code_unique'),
            'description_en.max' => __('services.description_max'),
            'description_ar.max' => __('services.description_max'),
            'standard_price.required' => __('services.standard_price_required'),
            'standard_price.numeric' => __('services.standard_price_numeric'),
            'standard_price.min' => __('services.standard_price_min'),
            'estimated_duration_minutes.integer' => __('services.duration_integer'),
            'estimated_duration_minutes.min' => __('services.duration_min'),
            'tax_percentage.numeric' => __('services.tax_numeric'),
            'tax_percentage.min' => __('services.tax_min'),
            'tax_percentage.max' => __('services.tax_max'),
            'reminder_interval_days.integer' => __('services.reminder_integer'),
            'reminder_interval_days.min' => __('services.reminder_min'),
            'mileage_interval.integer' => __('services.mileage_integer'),
            'mileage_interval.min' => __('services.mileage_min'),
            'mappings.*.product_id.exists' => __('services.selected_product_missing'),
            'mappings.*.quantity.integer' => __('services.mapped_quantity_integer'),
            'mappings.*.quantity.min' => __('services.mapped_quantity_min'),
            'mappings.*.unit.max' => __('services.mapping_unit_max'),
        ];
    }

    public function attributes(): array
    {
        return [
            'title_en' => __('services.title_en'),
            'title_ar' => __('services.title_ar'),
            'description_en' => __('services.description_en'),
            'description_ar' => __('services.description_ar'),
        ];
    }
}
