<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateTeamRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'workspace_id' => 'required|exists:workspaces,_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,_id',
            'settings' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'workspace_id.required' => 'Workspace ID is required',
            'workspace_id.exists' => 'Selected workspace does not exist',
            'name.required' => 'Team name is required',
            'name.string' => 'Team name must be a string',
            'name.max' => 'Team name must not exceed 255 characters',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must not exceed 1000 characters',
            'user_ids.array' => 'User IDs must be an array',
            'user_ids.*.exists' => 'One or more selected users do not exist',
            'settings.array' => 'Settings must be an array',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $errors[0], // Return first error message
                'errors' => $errors
            ], 422)
        );
    }
}
