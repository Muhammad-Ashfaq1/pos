<?php

namespace App\Http\Requests\Tenant\Products;

use App\Models\Product;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $productId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where(
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
            'sub_category_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_categories', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $value) {
                        return;
                    }

                    $categoryId = $this->input('category_id');

                    if (! $categoryId) {
                        $fail('Please select a category before choosing a sub category.');

                        return;
                    }

                    $belongsToCategory = \App\Models\SubCategory::query()
                        ->whereKey($value)
                        ->where('category_id', $categoryId)
                        ->exists();

                    if (! $belongsToCategory) {
                        $fail('The selected sub category does not belong to the selected category.');
                    }
                },
            ],
            'product_type' => [
                'required',
                'string',
                Rule::in(array_keys(Product::typeOptions())),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('products', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],
            'slug' => ['nullable', 'string', 'max:170'],
            'sku' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('products', 'barcode')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],
            'brand' => ['nullable', 'string', 'max:120'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'opening_stock' => ['nullable', 'numeric', 'min:0'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock_level' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'track_inventory' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected product was not found for this shop.',
            'category_id.exists' => 'The selected category was not found for this shop.',
            'sub_category_id.exists' => 'The selected sub category was not found for this shop.',
            'product_type.required' => 'Please select a product type.',
            'product_type.in' => 'Please select a valid product type.',
            'name.required' => 'Please enter a product name.',
            'name.max' => 'The product name may not be greater than 150 characters.',
            'name.unique' => 'This product name already exists for this shop.',
            'sku.max' => 'The SKU may not be greater than 80 characters.',
            'sku.unique' => 'This SKU already exists for this shop.',
            'barcode.max' => 'The barcode may not be greater than 80 characters.',
            'barcode.unique' => 'This barcode already exists for this shop.',
            'brand.max' => 'The brand may not be greater than 120 characters.',
            'unit.max' => 'The unit may not be greater than 50 characters.',
            'description.max' => 'The description may not be greater than 2000 characters.',
        ];
    }
}
