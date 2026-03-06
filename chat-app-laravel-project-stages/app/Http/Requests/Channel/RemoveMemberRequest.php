<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Channel;

class RemoveMemberRequest extends FormRequest
{
    protected $channel;

    public function authorize()
    {
        // Channel is injected by ChannelExistMiddleware
        $this->channel = $this->attributes->get('channel');

        // Fallback for direct invocation without middleware
        if (!$this->channel && $this->route('id')) {
            $this->channel = Channel::where('_id', $this->route('id'))->first();
        }

        return $this->channel !== null;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,_id',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $userId = $data['user_id'];
        $channel = $this->channel;
        $isDirect = (string) $channel->type === 'direct';

        if ($isDirect) {
            abort(422, 'Cannot remove members from a direct channel');
        }

        $members = collect($channel->members ?? []);

        // Check if user is actually a member
        $isMember = $members->contains(
            fn ($member) => (string) ($member['user_id'] ?? '') === (string) $userId
        );

        if (!$isMember) {
            abort(400, 'User is not a member of this channel');
        }

        // Remove the member
        $data['members'] = $members
            ->reject(fn ($member) => (string) ($member['user_id'] ?? '') === (string) $userId)
            ->values()
            ->all();

        return $data;
    }
}
