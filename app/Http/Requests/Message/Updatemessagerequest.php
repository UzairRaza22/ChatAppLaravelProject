<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
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
            'message'    => 'nullable|string|max:5000',
            'file'       => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp4,mp3',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('message') && !$this->hasFile('file')) {
                $validator->errors()->add('message', 'Update must include message content or a new file.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'channel_id.required' => 'channel_id is required.',
            'message_id.required' => 'message_id is required.',
            'file.max'            => 'File size must not exceed 10MB.',
            'file.mimes'          => 'File type not allowed.',
        ];
    }
}
