<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Channel;

class UpdateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channel = $this->attributes->get('channel')
            ?? Channel::where('_id', $this->route('id'))->first();

        if (!$channel) {
            return [
                'name' => 'required|string',
                'type' => 'required|in:public,private,direct',
            ];
        }

        $workspaceId = $this->input('workspace_id', $channel->workspace_id ?? null);
        $teamId = $this->input('team_id', $channel->team_id ?? null);
        $type = (string) ($channel->type ?? '');

        $nameRule = 'required|string|unique:channels,name,' . $this->route('id') . ',_id,workspace_id,' . $workspaceId;
        if ($type !== 'direct') {
            $nameRule .= ',team_id,' . $teamId;
        }

        return [
            'name' => $nameRule,
            'type' => 'required|in:' . $type,
        ];
    }
}
