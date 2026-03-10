<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class RemoveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'workspace_id' => 'required|string',
            'team_id'      => 'required|string',
            'user_ids'     => 'required|array|min:1',
            'user_ids.*'   => 'required|string', 
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Please provide at least one user ID to remove.',
            'user_ids.*.string' => 'User ID must be a valid string.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_ids.*' => 'user ID',
        ];
    }
}