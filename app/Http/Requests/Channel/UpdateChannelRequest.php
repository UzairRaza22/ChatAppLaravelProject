<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channel = request()->attributes->get('channel');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('channels', 'name')
                    ->ignore($channel?->_id, '_id')
                    ->where(fn ($query) => $query
                        ->where('workspace_id', $channel?->workspace_id)
                        ->where('team_id', $channel?->team_id)),
            ],
            'type' => 'required|in:public,private,direct',
        ];
    }
}
