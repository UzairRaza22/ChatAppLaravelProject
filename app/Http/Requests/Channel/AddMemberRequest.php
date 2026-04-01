<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;

class AddMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'channel_id' => 'required',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,_id',
        ];
    }
}
