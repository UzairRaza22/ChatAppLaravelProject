<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'team_id'     => 'required|string',
            'name'        => 'required|string|min:3|max:50',
            'description' => 'nullable|string|max:255',
        ];
    }
}