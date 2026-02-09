<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'sometimes|required|string|max:4000',
            'message_type' => 'nullable|in:text,file,image',
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'Content is required',
            'content.string' => 'Content must be a string',
            'content.max' => 'Content must not exceed 4000 characters',
            'message_type.in' => 'Message type must be one of: text, file, image',
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