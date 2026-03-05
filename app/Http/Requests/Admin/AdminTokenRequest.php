<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Everyone can attempt, middleware will handle actual admin check
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string|size:42', // assuming your token length is 42 chars
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Admin token is required',
            'token.size' => 'Invalid admin token length',
        ];
    }
}
