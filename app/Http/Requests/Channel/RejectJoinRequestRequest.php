<?php

namespace App\Http\Requests\Channel;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;

class RejectJoinRequestRequest extends FormRequest
{
    protected $channel;

    public function authorize(): bool
    {
        $this->channel = $this->attributes->get('channel');

        if (!$this->channel && $this->route('id')) {
            $this->channel = Channel::where('_id', $this->route('id'))->first();
        }

        return $this->channel !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,_id',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $channel = $this->channel;
        $userId = (string) $data['user_id'];

        if ((string) $channel->type !== 'public') {
            abort(403, 'Only public channels use join requests');
        }

        $joinRequests = collect($channel->join_requests ?? []);

        $hasPending = $joinRequests->contains(function ($joinRequest) use ($userId) {
            return (string) data_get($joinRequest, 'user_id') === $userId
                && (string) data_get($joinRequest, 'status') === 'pending';
        });

        if (!$hasPending) {
            abort(400, 'No pending join request for this user');
        }

        $remainingJoinRequests = $joinRequests
            ->reject(function ($joinRequest) use ($userId) {
                return (string) data_get($joinRequest, 'user_id') === $userId
                    && (string) data_get($joinRequest, 'status') === 'pending';
            })
            ->values()
            ->all();

        return [
            'join_requests' => $remainingJoinRequests,
        ];
    }
}