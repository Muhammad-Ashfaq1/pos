<?php

namespace App\Http\Requests\Employee\Orders;

use App\Models\Vehicle;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->filled('customer_id') ? (int) $this->input('customer_id') : null,
            'vehicle_id' => $this->filled('vehicle_id') ? (int) $this->input('vehicle_id') : null,
            'notes' => $this->normalizeNullableString($this->input('notes')),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();

        return [
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'vehicle_id' => [
                'nullable',
                'integer',
                Rule::exists('vehicles', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                ),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('customer_id') || ! $this->filled('vehicle_id')) {
                return;
            }

            $belongsToCustomer = Vehicle::query()
                ->whereKey($this->integer('vehicle_id'))
                ->where('customer_id', $this->integer('customer_id'))
                ->exists();

            if (! $belongsToCustomer) {
                $validator->errors()->add('vehicle_id', 'The selected vehicle does not belong to the selected customer.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one item before saving the order.',
            'items.min' => 'Please add at least one item before saving the order.',
            'items.*.product_id.exists' => 'One or more selected products are no longer available.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
