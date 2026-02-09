<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTeamRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:teams,_id',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Team ID is required',
            'id.exists' => 'Selected team does not exist',
        ];
    }
}
