<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class RemoveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'team_id'   => 'required|string',
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