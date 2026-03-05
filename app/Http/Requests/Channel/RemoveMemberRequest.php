<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Channel;

class RemoveMemberRequest extends FormRequest
{
    protected $channel;

    public function authorize()
    {
        // Channel is injected via route middleware
        $this->channel = $this->route('id') ? Channel::where('_id', $this->route('id'))->first() : null;
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

        $members = collect($channel->members ?? []);

        // Check if user is actually a member
        if ($members->where('user_id', $userId)->count() == 0) {
            abort(400, 'User is not a member of this channel');
        }

        // Remove the member
        $data['members'] = $members->reject(fn($m) => $m['user_id'] == $userId)->values()->all();

        return $data;
    }
}