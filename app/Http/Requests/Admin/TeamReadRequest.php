<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Team;

class TeamReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            'team_id' => ['nullable', 'exists:teams,_id'],
        ];
    }

    public function validatedTeam()
    {
        return $this->filled('team_id')
            ? Team::findOrFail($this->team_id)
            : Team::all();
    }
}
