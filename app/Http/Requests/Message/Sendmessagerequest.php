<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // workspace_id NOT required — resolved from receiver or channel
            'receiver_id'  => 'nullable|string',
            'channel_id'   => 'nullable|string',
            'message_type' => 'required|string|in:text,file',
            'content'      => 'nullable|string|max:5000',
            'file'         => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp4,mp3',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasReceiver = $this->filled('receiver_id');
            $hasChannel  = $this->filled('channel_id');

            // Must have one of receiver_id or channel_id
            if (!$hasReceiver && !$hasChannel) {
                $validator->errors()->add('receiver_id', 'Either receiver_id or channel_id is required.');
            }

            // Cannot have both
            if ($hasReceiver && $hasChannel) {
                $validator->errors()->add('receiver_id', 'Provide either receiver_id or channel_id, not both.');
            }

            // Must have content or file
            if (!$this->filled('content') && !$this->hasFile('file')) {
                $validator->errors()->add('content', 'Either content or file is required.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'message_type.required' => 'message_type is required.',
            'message_type.in'       => 'message_type must be text or file.',
        ];
    }
}
