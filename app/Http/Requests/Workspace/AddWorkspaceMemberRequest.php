<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class AddWorkspaceMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'workspace_id' => 'required|string',

            // Accept either user_ids, user_emails, or both
            'user_ids'   => 'array|nullable',
            'user_ids.*' => 'string|nullable',

            'user_emails'   => 'array|nullable',
            'user_emails.*' => 'email|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.*.string'     => 'Each user ID must be a string.',
            'user_emails.*.email'   => 'The email :input is not valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_ids.*'   => 'user ID',
            'user_emails.*' => 'email',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $userIds = $this->input('user_ids', []);
            $userEmails = $this->input('user_emails', []);

            // Ensure at least one is provided
            if (empty(array_filter($userIds)) && empty(array_filter($userEmails))) {
                $validator->errors()->add('users', 'Please provide at least one user ID or email address.');
            }
        });
    }
}
