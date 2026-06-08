<?php

namespace App\Http\Requests\Tenant\ProductTypes;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProductTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $productTypeId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('product_types', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('product_types', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productTypeId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:170',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('product_types', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productTypeId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected product type was not found for this shop.',
            'name.required' => 'Please enter a product type name.',
            'name.max' => 'The product type name may not be greater than 150 characters.',
            'name.unique' => 'This product type name already exists for this shop.',
            'code.max' => 'The product type code may not be greater than 50 characters.',
            'code.unique' => 'This product type code already exists for this shop.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'sort_order.integer' => 'Sort order must be a whole number.',
            'sort_order.min' => 'Sort order must be zero or greater.',
        ];
    }
}
