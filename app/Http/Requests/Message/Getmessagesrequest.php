<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class GetMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'channel_id.required' => 'channel_id is required.',
        ];
    }
}