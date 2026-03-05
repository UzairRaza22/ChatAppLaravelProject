<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class ImpersonateReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,_id'],
        ];
    }

    public function validatedUser()
    {
        return User::findOrFail($this->user_id);
    }
}
