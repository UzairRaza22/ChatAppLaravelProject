<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateChannelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'workspace_id' => 'required|exists:workspaces,_id',
            'team_id' => 'required|exists:teams,_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:text,voice',
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
            'team_id.required' => 'Team ID is required',
            'team_id.exists' => 'Selected team does not exist',
            'name.required' => 'Channel name is required',
            'name.string' => 'Channel name must be a string',
            'name.max' => 'Channel name must not exceed 255 characters',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must not exceed 1000 characters',
            'type.required' => 'Channel type is required',
            'type.string' => 'Channel type must be a string',
            'type.in' => 'Channel type must be either text or voice',
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