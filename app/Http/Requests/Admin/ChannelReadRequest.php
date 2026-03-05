<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Channel;

class ChannelReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin_id !== null;
    }

    public function rules(): array
    {
        return [
            'channel_id' => ['nullable', 'exists:channels,_id'],
        ];
    }

    public function validatedChannel()
    {
        return $this->filled('channel_id')
            ? Channel::findOrFail($this->channel_id)
            : Channel::all();
    }
}
