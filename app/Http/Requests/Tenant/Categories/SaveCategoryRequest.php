<?php

namespace App\Http\Requests\Tenant\Categories;

use App\Models\Category;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCategoryRequest extends FormRequest
{
    private ?Category $resolvedCategory = null;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $category = $this->category();

        return $category
            ? $user->can('update', $category)
            : $user->can('create', Category::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->filled('id') ? (int) $this->id : null,
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'code' => blank($this->code) ? null : trim((string) $this->code),
            'description' => blank($this->description) ? null : trim((string) $this->description),
            'sort_order' => $this->filled('sort_order') ? (int) $this->sort_order : 0,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $categoryId = $this->category()?->id;

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
            'name.unique' => 'This category name is already in use for this shop.',
            'code.max' => 'The category code may not be greater than 50 characters.',
            'code.unique' => 'This category code is already in use for this shop.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'sort_order.integer' => 'Sort order must be a whole number.',
            'sort_order.min' => 'Sort order must be zero or greater.',
        ];
    }

    public function category(): ?Category
    {
        if (! $this->filled('id')) {
            return null;
        }

        return $this->resolvedCategory ??= Category::query()->find($this->integer('id'));
    }

    public function payload(): array
    {
        return $this->safe()->except('id');
    }
}
