<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class RemoveWorkspaceMemberRequest extends FormRequest
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
            'emails' => 'required|array',
            'emails.*' => 'email|exists:users,email'
        ];
    }

    public function messages(): array
    {
        return [
            'emails.*.exists' => 'The email :input is not registered.',
        ];
    }

    public function attributes(): array
    {
        return [
            'emails.*' => 'email address',
        ];
    }
}
