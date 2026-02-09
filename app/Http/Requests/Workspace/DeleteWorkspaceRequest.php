<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class DeleteWorkspaceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:workspaces,_id',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Workspace ID is required',
            'id.exists' => 'Selected workspace does not exist',
        ];
    }
}
