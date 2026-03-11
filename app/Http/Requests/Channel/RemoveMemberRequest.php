<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;

class RemoveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'channel_id' => 'required',
            'user_id' => 'required|exists:users,_id',
            'members' => 'sometimes|array',
            'members.*.user_id' => 'sometimes|string',
            'members.*.role' => 'sometimes|string',
        ];
    }
}
