<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;

class DeleteChannelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:channels,_id',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Channel ID is required',
            'id.exists' => 'Selected channel does not exist',
        ];
    }
}
