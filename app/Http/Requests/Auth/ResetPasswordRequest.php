<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ResetPasswordRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Email field is required',
            'email.email' => 'Please provide a valid email address',
            'email.exists' => 'This email is not registered in our system',
            'otp.required' => 'OTP field is required',
            'otp.string' => 'OTP must be a string',
            'otp.size' => 'OTP must be exactly 6 digits',
            'password.required' => 'Password field is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 8 characters long',
            'password.confirmed' => 'Password confirmation does not match',
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