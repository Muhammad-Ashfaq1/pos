<?php

namespace App\Http\Requests\Tenant\Vehicles;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'plate_number' => strtoupper(trim((string) $this->input('plate_number'))),
            'registration_number' => $this->normalizeNullableUpperString($this->input('registration_number')),
            'make' => $this->normalizeNullableString($this->input('make')),
            'model' => $this->normalizeNullableString($this->input('model')),
            'color' => $this->normalizeNullableString($this->input('color')),
            'engine_type' => $this->normalizeNullableString($this->input('engine_type')),
            'notes' => $this->normalizeNullableString($this->input('notes')),
        ]);
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->id();
        $vehicleId = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => [
                'nullable',
                'integer',
                Rule::exists('vehicles', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'plate_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicles', 'plate_number')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($vehicleId),
            ],
            'registration_number' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('vehicles', 'registration_number')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($vehicleId),
            ],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.((int) now()->year + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'engine_type' => ['nullable', 'string', 'max:80'],
            'odometer' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_default' => ['required', 'boolean'],
            'tenant_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The selected vehicle was not found for this shop.',
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer was not found for this shop.',
            'plate_number.required' => 'Please enter a plate number.',
            'plate_number.max' => 'The plate number may not be greater than 50 characters.',
            'plate_number.unique' => 'This plate number already exists for this shop.',
            'registration_number.max' => 'The registration number may not be greater than 80 characters.',
            'registration_number.unique' => 'This registration number already exists for this shop.',
            'make.max' => 'The make may not be greater than 100 characters.',
            'model.max' => 'The model may not be greater than 100 characters.',
            'year.integer' => 'Year must be a whole number.',
            'year.min' => 'Year must be 1900 or greater.',
            'year.max' => 'Please enter a realistic vehicle year.',
            'color.max' => 'The color may not be greater than 50 characters.',
            'engine_type.max' => 'The engine type may not be greater than 80 characters.',
            'odometer.numeric' => 'The odometer must be numeric.',
            'odometer.min' => 'The odometer must be zero or greater.',
            'notes.max' => 'The notes may not be greater than 2000 characters.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeNullableUpperString(mixed $value): ?string
    {
        $value = $this->normalizeNullableString($value);

        return $value !== null ? strtoupper($value) : null;
    }
}
