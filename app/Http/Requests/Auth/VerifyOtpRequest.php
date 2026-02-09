<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'type' => 'required|in:registration,forgot_password'
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
            'email.exists' => 'This email is not registered in our system',
            'otp.required' => 'OTP field is required',
            'otp.string' => 'OTP must be a string',
            'otp.size' => 'OTP must be exactly 6 digits',
            'type.required' => 'OTP type is required',
            'type.in' => 'OTP type must be either registration or forgot_password',
        ];
    }
}