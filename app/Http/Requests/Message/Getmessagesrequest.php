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
            // workspace_id NOT required — resolved from receiver or channel
            'receiver_id' => 'nullable|string',
            'channel_id'  => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasReceiver = $this->filled('receiver_id');
            $hasChannel  = $this->filled('channel_id');

            if (!$hasReceiver && !$hasChannel) {
                $validator->errors()->add('receiver_id', 'Either receiver_id or channel_id is required.');
            }

            if ($hasReceiver && $hasChannel) {
                $validator->errors()->add('receiver_id', 'Provide either receiver_id or channel_id, not both.');
            }
        });
    }
}
