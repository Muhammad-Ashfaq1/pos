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
        $serviceFees = collect($this->input('service_fees', []))
            ->filter(fn ($fee) => is_array($fee))
            ->map(fn (array $fee) => [
                'type' => trim((string) ($fee['type'] ?? '')),
                'service_id' => filled($fee['service_id'] ?? null) ? (int) $fee['service_id'] : null,
                'name' => $this->normalizeNullableString($fee['name'] ?? null),
                'amount' => filled($fee['amount'] ?? null) ? (float) $fee['amount'] : null,
            ])
            ->values()
            ->all();

        $this->merge([
            'customer_id' => $this->filled('customer_id') ? (int) $this->input('customer_id') : null,
            'vehicle_id' => $this->filled('vehicle_id') ? (int) $this->input('vehicle_id') : null,
            'service_fees' => $serviceFees,
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
            'service_fees' => ['nullable', 'array', 'max:50'],
            'service_fees.*.type' => ['required', Rule::in(['service', 'manual'])],
            'service_fees.*.service_id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where(
                    fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                ),
            ],
            'service_fees.*.name' => ['nullable', 'string', 'max:150'],
            'service_fees.*.amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'payment' => ['required', 'array'],
            'payment.method' => ['required', Rule::in(['cash', 'card', 'check'])],
            'payment.amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateServiceFees($validator);

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
            'service_fees.max' => 'You can add up to 50 service fees on one order.',
            'service_fees.*.type.in' => 'Please select a valid service fee type.',
            'service_fees.*.service_id.exists' => 'One or more selected services are no longer available.',
            'service_fees.*.name.max' => 'Manual service fee title may not be greater than 150 characters.',
            'service_fees.*.amount.min' => 'Service fee amount cannot be negative.',
            'service_fees.*.amount.max' => 'Service fee amount is too large.',
            'payment.required' => 'Please enter payment details before checkout.',
            'payment.method.required' => 'Please select a payment method.',
            'payment.method.in' => 'Please select a valid payment method.',
            'payment.amount.required' => 'Please enter the payment amount.',
            'payment.amount.min' => 'Payment amount cannot be negative.',
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

    private function validateServiceFees(Validator $validator): void
    {
        $serviceFees = $this->input('service_fees', []);
        $selectedServiceIds = [];

        foreach ($serviceFees as $index => $fee) {
            $type = $fee['type'] ?? null;
            $serviceId = $fee['service_id'] ?? null;

            if ($type === 'service') {
                if (! $serviceId) {
                    $validator->errors()->add("service_fees.{$index}.service_id", 'Please select a service.');
                    continue;
                }
            }

            if ($serviceId) {
                $serviceKey = "{$type}:{$serviceId}";

                if (in_array($serviceKey, $selectedServiceIds, true)) {
                    $validator->errors()->add("service_fees.{$index}.service_id", 'This service is already added to the order.');
                    continue;
                }

                $selectedServiceIds[] = $serviceKey;
            }

            if ($type === 'manual') {
                if (! filled($fee['name'] ?? null)) {
                    $validator->errors()->add("service_fees.{$index}.name", 'Please enter a manual service fee title.');
                }

                if (! filled($fee['amount'] ?? null) || (float) $fee['amount'] <= 0) {
                    $validator->errors()->add("service_fees.{$index}.amount", 'Please enter a manual service fee amount.');
                }
            }
        }
    }
}
