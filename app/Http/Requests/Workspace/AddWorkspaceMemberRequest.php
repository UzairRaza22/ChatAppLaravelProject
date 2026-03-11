<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class AddWorkspaceMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'workspace_id'=>'required|exists:workspaces,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,_id'
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.*.exists' => 'The user ID :input is not registered.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_ids.*' => 'user ID',
        ];
    }
}