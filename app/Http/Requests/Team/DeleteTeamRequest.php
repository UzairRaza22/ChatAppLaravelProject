<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTeamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'team_id' => 'required|string',
        ];
    }
}