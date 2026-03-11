<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_id' => 'required|string',
            'message_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'channel_id.required' => 'channel_id is required.',
            'message_id.required' => 'message_id is required.',
        ];
    }
}
