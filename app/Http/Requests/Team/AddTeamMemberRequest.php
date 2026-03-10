<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
       return [
            'team_id'      => 'required|string',
            'workspace_id' => 'required|string',
            'user_ids'     => 'required|array|min:1',
            'user_ids.*'   => 'required|string', 
        ];
    }

    public function messages(): array
    {
       return [
            'user_ids.required' => 'Please provide at least one user ID.',
            'user_ids.*.string' => 'User ID must be a valid string.',
        ];
    }
}