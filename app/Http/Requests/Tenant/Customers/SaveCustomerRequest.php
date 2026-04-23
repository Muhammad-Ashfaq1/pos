<?php

namespace App\Http\Requests\Tenant\Customers;

use App\Models\Customer;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $this->normalizeNullableString($this->input('phone')),
            'email' => $this->normalizeNullableString(mb_strtolower((string) $this->input('email'))),
            'address' => $this->normalizeNullableString($this->input('address')),
            'notes' => $this->normalizeNullableString($this->input('notes')),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $customerId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'customer_type' => [
                'required',
                'string',
                Rule::in(array_keys(Customer::typeOptions())),
            ],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'date_of_birth' => ['nullable', 'date'],
            'total_visits' => ['nullable', 'integer', 'min:0'],
            'lifetime_value' => ['nullable', 'numeric', 'min:0'],
            'loyalty_points_balance' => ['nullable', 'integer', 'min:0'],
            'credit_balance' => ['nullable', 'numeric'],
            'last_visit_at' => ['nullable', 'date'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected customer was not found for this shop.',
            'customer_type.required' => 'Please select a customer type.',
            'customer_type.in' => 'Please select a valid customer type.',
            'name.required' => 'Please enter a customer name.',
            'name.max' => 'The customer name may not be greater than 150 characters.',
            'phone.max' => 'The phone number may not be greater than 30 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email may not be greater than 150 characters.',
            'address.max' => 'The address may not be greater than 1000 characters.',
            'notes.max' => 'The notes may not be greater than 2000 characters.',
            'total_visits.integer' => 'Total visits must be a whole number.',
            'total_visits.min' => 'Total visits must be zero or greater.',
            'lifetime_value.numeric' => 'Lifetime value must be numeric.',
            'lifetime_value.min' => 'Lifetime value must be zero or greater.',
            'loyalty_points_balance.integer' => 'Loyalty points must be a whole number.',
            'loyalty_points_balance.min' => 'Loyalty points must be zero or greater.',
            'credit_balance.numeric' => 'Credit balance must be numeric.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
