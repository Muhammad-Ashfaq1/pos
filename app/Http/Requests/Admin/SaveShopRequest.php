<?php

namespace App\Http\Requests\Admin;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->filled('id') ? (int) $this->input('id') : null;
        $tenant = $tenantId ? Tenant::query()->with('adminUser')->find($tenantId) : null;
        $adminUserId = $tenant?->adminUser?->id;

        return [
            'id' => ['nullable', 'integer', 'exists:tenants,id'],
            'owner_name' => ['required', 'string', 'max:150'],
            'owner_email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('tenants', 'owner_email')->ignore($tenantId),
                Rule::unique('users', 'email')->ignore($adminUserId),
            ],
            'password' => [
                $tenantId ? 'nullable' : 'required',
                'string',
                'min:8',
            ],
            'shop_name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::enum(TenantStatus::class)],
            'website_url' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^https?:\/\/[^\s\/$.?#].[^\s]*$/i',
            ],
            'business_type' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'plan_expires_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_name.required' => 'Please enter the owner name.',
            'owner_name.max' => 'Owner name may not exceed 150 characters.',
            'owner_email.required' => 'Please enter the owner email.',
            'owner_email.email' => 'Please enter a valid email address.',
            'owner_email.unique' => 'This email address is already registered.',
            'password.required' => 'Please enter a password for the shop admin.',
            'password.min' => 'Password must be at least 8 characters long.',
            'shop_name.required' => 'Please enter the shop name.',
            'status.required' => 'Please select a shop status.',
            'status.in' => 'The selected shop status is invalid.',
            'website_url.regex' => 'Website URL must be a valid web address starting with http:// or https://',
        ];
    }
}
