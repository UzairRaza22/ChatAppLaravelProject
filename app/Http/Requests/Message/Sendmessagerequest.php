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
            'channel_id' => 'required|string',
            'message'    => 'nullable|string|max:5000',
            'file'       => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp4,mp3',
            'schedule_time' => 'nullable|date',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('message') && !$this->hasFile('file')) {
                $validator->errors()->add('message', 'Either message or file is required.');
            }

            if ($this->filled('schedule_time')) {
                try {
                    $scheduleTime = \Illuminate\Support\Carbon::parse(
                        $this->input('schedule_time'),
                        'Asia/Karachi'
                    );
                    $nowPtk = \Illuminate\Support\Carbon::now('Asia/Karachi');

                    if ($scheduleTime->lessThanOrEqualTo($nowPtk)) {
                        $validator->errors()->add('schedule_time', 'schedule_time must be a future datetime in Pakistan.');
                    }
                } catch (\Throwable $e) {
                    $validator->errors()->add('schedule_time', 'schedule_time must be a valid datetime.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'channel_id.required' => 'channel_id is required.',
            'file.max'            => 'File size must not exceed 10MB.',
            'file.mimes'          => 'File type not allowed.',
        ];
    }
}
