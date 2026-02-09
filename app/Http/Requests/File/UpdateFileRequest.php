<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'filename' => 'nullable|string|max:255',
            'original_name' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'filename.string' => 'Filename must be a string',
            'filename.max' => 'Filename must not exceed 255 characters',
            'original_name.string' => 'Original name must be a string',
            'original_name.max' => 'Original name must not exceed 255 characters',
            'metadata.array' => 'Metadata must be an array',
        ];
    }
}
