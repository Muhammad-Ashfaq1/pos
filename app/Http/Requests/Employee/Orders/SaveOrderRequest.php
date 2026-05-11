<?php

namespace App\Http\Requests\Employee\Orders;

use App\Models\Customer;
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
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'vehicle_id' => [
                'required',
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
            'payment' => ['required', 'array'],
            'payment.method' => ['required', Rule::in(['cash', 'card', 'check'])],
            'payment.amount' => ['required', 'numeric', 'min:0.01'],
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

            $customer = Customer::query()->find($this->integer('customer_id'));
            $vehicle = Vehicle::query()->find($this->integer('vehicle_id'));

            if (! $customer || ! $vehicle) {
                return;
            }

            if ((int) $vehicle->customer_id !== $this->integer('customer_id')) {
                $validator->errors()->add('vehicle_id', 'The selected vehicle does not belong to the selected customer.');
            }

            if (! $this->hasCustomerDetails($customer)) {
                $validator->errors()->add('customer_id', 'Please add customer details before saving the order.');
            }

            if (! $this->hasVehicleDetails($vehicle)) {
                $validator->errors()->add('vehicle_id', 'Please add vehicle details before saving the order.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer before saving the order.',
            'customer_id.exists' => 'The selected customer is no longer available.',
            'vehicle_id.required' => 'Please select a vehicle before saving the order.',
            'vehicle_id.exists' => 'The selected vehicle is no longer available.',
            'items.required' => 'Please add at least one item before saving the order.',
            'items.min' => 'Please add at least one item before saving the order.',
            'items.*.product_id.exists' => 'One or more selected products are no longer available.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
            'payment.required' => 'Please enter payment details before checkout.',
            'payment.method.required' => 'Please select a payment method.',
            'payment.method.in' => 'Please select a valid payment method.',
            'payment.amount.required' => 'Please enter the payment amount.',
            'payment.amount.min' => 'Payment amount must be greater than zero.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function hasCustomerDetails(Customer $customer): bool
    {
        $name = trim((string) $customer->name);
        $hasRealName = $name !== '' && $name !== Customer::defaultWalkInName();

        return $hasRealName
            || filled($customer->phone)
            || filled($customer->email)
            || filled($customer->address);
    }

    private function hasVehicleDetails(Vehicle $vehicle): bool
    {
        return filled($vehicle->plate_number)
            || filled($vehicle->registration_number)
            || filled($vehicle->make)
            || filled($vehicle->model);
    }
}
