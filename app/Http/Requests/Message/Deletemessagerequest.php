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
            'message_id' => 'required|string',
            // workspace_id NOT required — resolved from message itself
        ];
    }
}
