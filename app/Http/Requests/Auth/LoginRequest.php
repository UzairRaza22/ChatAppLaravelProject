<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'otp' => 'sometimes|string|size:6'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Email field is required',
            'email.email' => 'Please provide a valid email address',
            'password.required' => 'Password field is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 6 characters long',
            'otp.sometimes' => 'OTP field validation failed',
            'otp.string' => 'OTP must be a string',
            'otp.size' => 'OTP must be exactly 6 digits',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $errors[0], // Return first error message
                'errors' => $errors
            ], 422)
        );
    }
}