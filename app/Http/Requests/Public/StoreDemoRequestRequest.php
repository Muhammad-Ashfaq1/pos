<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemoRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('home.demo_modal.name'),
            'business_name' => __('home.demo_modal.business_name'),
            'email' => __('app.email'),
            'phone' => __('app.phone'),
            'business_type' => __('home.demo_modal.business_type'),
            'message' => __('home.demo_modal.message'),
        ];
    }
}
