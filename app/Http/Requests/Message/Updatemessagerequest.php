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
            'message_id' => 'required|string',
            // workspace_id NOT required — resolved from message itself
            'content'    => 'nullable|string|max:5000',
            'file'       => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp4,mp3',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'   => 'File size must not exceed 10MB.',
            'file.mimes' => 'File type not allowed.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('content') && !$this->hasFile('file')) {
                $validator->errors()->add('content', 'Update must include content or a new file.');
            }
        });
    }
}
