<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTenantStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'action' => $this->route('action'),
        ]);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'reject', 'suspend', 'reactivate'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
