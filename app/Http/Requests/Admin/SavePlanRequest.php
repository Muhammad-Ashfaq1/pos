<?php

namespace App\Http\Requests\Admin;

use App\Enums\PlanDuration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:plans,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_type' => ['required', Rule::enum(PlanDuration::class)],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
