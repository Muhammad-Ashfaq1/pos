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
        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $id       = $this->route('discount_group') ? $this->route('discount_group')->id : $this->id;
        $slug     = Str::slug($this->title);

        return [
            'title'     => [
                'required',
                'string',
                'max:255',
                Rule::unique('discount_groups', 'name')
                    ->where('tenant_id', $tenantId)
                    ->ignore($id),
            ],
            'type'      => 'required|in:percentage,fixed',
            'value'     => [
                'required',
                'numeric',
                'min:0',
                $this->type === 'percentage' ? 'max:100' : 'lte:min_limit',
            ],
            'min_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'Customer Discount Group already exists in records',
            'value.max'    => 'The discount percentage cannot exceed 100%.',
            'value.lte'    => 'The discount amount cannot exceed the minimum purchase limit.',
        ];
    }
}
