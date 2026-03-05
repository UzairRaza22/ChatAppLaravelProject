<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Message;

class MessageReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            'message_id' => ['nullable', 'exists:messages,_id'],
        ];
    }

    public function validatedMessage()
    {
        return $this->filled('message_id')
            ? Message::findOrFail($this->message_id)
            : Message::all();
    }
}
