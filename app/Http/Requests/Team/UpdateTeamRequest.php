<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'workspace_id' => 'required|string',
            'team_id'      => 'required|string',
            'description'  => 'nullable|string|max:255',
            'name'         => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('teams', 'name')
                    ->where('workspace_id', $this->input('workspace_id'))
                    ->ignore($this->input('team_id'))
            ],
        ];
    }
}