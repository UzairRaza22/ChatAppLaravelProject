<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'channel_id' => 'required|exists:channels,_id',
            'content' => 'required|string|max:4000',
            'message_type' => 'nullable|in:text,file,image',
            'reply_to_id' => 'nullable|exists:messages,_id',
            'mentions' => 'nullable|array',
            'mentions.*' => 'exists:users,_id',
        ];
    }

    public function messages()
    {
        return [
            'channel_id.required' => 'Channel ID is required',
            'channel_id.exists' => 'Selected channel does not exist',
            'content.required' => 'Content is required',
            'content.string' => 'Content must be a string',
            'content.max' => 'Content must not exceed 4000 characters',
            'message_type.in' => 'Message type must be one of: text, file, image',
            'reply_to_id.exists' => 'Reply to message does not exist',
            'mentions.array' => 'Mentions must be an array',
            'mentions.*.exists' => 'One or more mentioned users do not exist',
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
