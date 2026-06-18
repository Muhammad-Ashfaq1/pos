<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateDiscountGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation()
    {
        $earnsCredit = $this->has('earns_credit');

        $this->merge([
            'is_active' => $this->has('is_active'),
            'earns_credit' => $earnsCredit,
            'credit_earn_type' => $this->input('credit_earn_type') ?: 'percentage',
            'credit_earn_rate' => $earnsCredit ? $this->input('credit_earn_rate', 0) : 0,
            'credit_min_spend' => $this->input('credit_min_spend') ?: 0,
        ]);
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $id = $this->route('discount_group') ? $this->route('discount_group')->id : $this->id;
        $slug = Str::slug($this->title);

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('discount_groups', 'name')
                    ->where('tenant_id', $tenantId)
                    ->ignore($id),
            ],
            'type' => 'required|in:percentage,fixed',
            'value' => [
                'required',
                'numeric',
                'min:0',
                $this->type === 'percentage' ? 'max:100' : 'lte:min_limit',
            ],
            'min_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'earns_credit' => 'boolean',
            'credit_earn_type' => 'nullable|in:percentage,fixed',
            'credit_earn_rate' => [
                $this->boolean('earns_credit') ? 'required' : 'nullable',
                'numeric',
                'min:0',
                $this->input('credit_earn_type', 'percentage') === 'percentage' ? 'max:100' : 'max:9999999.99',
            ],
            'credit_min_spend' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'Customer Discount Group already exists in records',
            'value.max' => 'The discount percentage cannot exceed 100%.',
            'value.lte' => 'The discount amount cannot exceed the minimum purchase limit.',
            'credit_earn_rate.required' => 'Please enter how much credit this group earns.',
            'credit_earn_rate.max' => 'The credit earn percentage cannot exceed 100%.',
        ];
    }
}
