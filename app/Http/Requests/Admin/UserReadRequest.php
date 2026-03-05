<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UserReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,_id'],
        ];
    }

    public function validatedUser()
    {
        return $this->filled('user_id')
            ? User::findOrFail($this->user_id)
            : User::all();
    }
}
