<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;

class CreateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:channels,name',
            'type' => 'required|in:public,private,direct',
            'workspace_id' => 'required|string',
            'team_id' => 'required|string',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $user = $this->user();

        if ($user) {
            $data['members'] = [[
                'user_id' => (string) $user->_id,
                'role' => 'admin',
            ]];
        }

        return $data;
    }
}
