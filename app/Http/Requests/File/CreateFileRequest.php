<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class CreateFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|max:10240', // 10MB max
            'channel_id' => 'nullable|exists:channels,_id',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'File is required',
            'file.file' => 'File must be a valid file',
            'file.max' => 'File size must not exceed 10MB',
            'channel_id.exists' => 'Selected channel does not exist',
        ];
    }
}
