<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware will enforce admin
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Admin token is required to read data',
        ];
    }
}
