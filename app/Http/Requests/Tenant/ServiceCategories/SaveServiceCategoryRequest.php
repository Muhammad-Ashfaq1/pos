<?php

namespace App\Http\Requests\Tenant\ServiceCategories;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slugSource = $this->filled('slug')
            ? (string) $this->input('slug')
            : (string) $this->input('name', '');

        $this->merge([
            'slug' => Str::slug($slugSource) ?: null,
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $categoryId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('service_categories', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('service_categories', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($categoryId),
            ],
            // Same as product categories: slug is normalized here, uniqueness enforced in repository.
            'slug' => [
                'nullable',
                'string',
                'max:170',
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'code' => ['prohibited'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected service category was not found for this shop.',
            'name.required' => 'Please enter a service category name.',
            'name.max' => 'The service category name may not be greater than 150 characters.',
            'name.unique' => 'This service category name already exists for this shop.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'sort_order.integer' => 'Sort order must be a whole number.',
            'sort_order.min' => 'Sort order must be zero or greater.',
        ];
    }
}
