<?php

namespace App\Http\Requests\Tenant\Products;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();

        return [
            'draw'            => ['nullable', 'integer'],
            'start'           => ['nullable', 'integer', 'min:0'],
            'length'          => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value'    => ['nullable', 'string', 'max:255'],
            'status'          => ['nullable', Rule::in(['1', '0', 'all'])],
            'category_id'     => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($q) => $q->where('tenant_id', $tenantId)
                ),
            ],
            'sub_category_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_categories', 'id')->where(
                    fn ($q) => $q->where('tenant_id', $tenantId)
                ),
            ],
            'product_type_id' => [
                'nullable',
                'integer',
                Rule::exists('product_types', 'id')->where(
                    fn ($q) => $q->where('tenant_id', $tenantId)
                ),
            ],
            'track_inventory' => ['nullable', Rule::in(['1', '0'])],
            'sort'            => ['nullable', Rule::in(['latest', 'name', 'price_low_high', 'stock_low_high'])],
            'columns'         => ['nullable', 'array'],
            'columns.*.data'  => ['nullable', 'string'],
            'order'           => ['nullable', 'array'],
            'order.*.column'  => ['nullable', 'integer', 'min:0'],
            'order.*.dir'     => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
