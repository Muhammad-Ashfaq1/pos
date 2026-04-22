<?php

namespace App\Http\Requests\Tenant\Categories;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $categoryId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
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
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($categoryId),
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
                Rule::unique('categories', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($categoryId),
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
            'id.exists' => 'The selected category was not found for this shop.',
            'name.required' => 'Please enter a category name.',
            'name.max' => 'The category name may not be greater than 150 characters.',
            'name.unique' => 'This category name already exists for this shop.',
            'code.max' => 'The category code may not be greater than 50 characters.',
            'code.unique' => 'This category code already exists for this shop.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'sort_order.integer' => 'Sort order must be a whole number.',
            'sort_order.min' => 'Sort order must be zero or greater.',
        ];
    }
}
