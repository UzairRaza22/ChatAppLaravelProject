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
            'workspace_id'  => 'required|string',
            'receiver_id'   => 'nullable|string',
            'channel_id'    => 'nullable|string',
            'message_type'  => 'required|in:text,file',
            'content'       => 'nullable|string|max:5000',
            'file'          => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp4,mp3',
        ];
    }

    public function messages(): array
    {
        return [
            'message_type.in'  => 'Message type must be text or file.',
            'file.max'         => 'File size must not exceed 10MB.',
            'file.mimes'       => 'File type not allowed.',
        ];
    }

    /**
     * Add custom validation: text message must have content,
     * file message must have a file, at least one must be present.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasContent = !empty($this->input('content'));
            $hasFile    = $this->hasFile('file');

            if (!$hasContent && !$hasFile) {
                $validator->errors()->add('content', 'A message must have either content or a file.');
            }
        });
    }
}
