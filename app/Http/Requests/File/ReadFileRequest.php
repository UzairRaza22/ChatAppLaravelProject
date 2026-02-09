<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class ReadFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:files,_id',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'File ID is required',
            'id.exists' => 'Selected file does not exist',
        ];
    }
}
