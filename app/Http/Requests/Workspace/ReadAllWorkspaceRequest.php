<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class ReadAllWorkspaceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'workspace_id' => 'nullable|exists:workspaces,_id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'workspace_id.exists' => 'Selected workspace does not exist',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must not exceed 100',
            'page.integer' => 'Page must be an integer',
            'page.min' => 'Page must be at least 1',
        ];
    }
}
