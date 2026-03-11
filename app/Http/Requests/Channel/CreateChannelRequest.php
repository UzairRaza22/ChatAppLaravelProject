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
        $type = (string) $this->input('type');
        $nameRule = 'required|string';

        if ($type !== 'direct') {
            $nameRule .= '|unique:channels,name,NULL,id,workspace_id,' . $this->workspace_id . ',team_id,' . $this->team_id;
        }

        return [
            'name' => $nameRule,
            'workspace_id' => 'required|exists:workspaces,_id',
            'team_id' => 'nullable|required_unless:type,direct|exists:teams,_id|prohibited_if:type,direct',
            'type' => 'required|in:public,private,direct',
            'direct_user_id' => 'sometimes|exists:users,_id',
            'id' => 'sometimes|string',
            'created_by' => 'sometimes|string',
            'created_id' => 'sometimes|string',
            'direct_id' => 'sometimes|string',
            'members' => 'sometimes|array',
            'members.*.user_id' => 'sometimes|string',
            'members.*.role' => 'sometimes|string',
        ];
    }
}
