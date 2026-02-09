<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use App\Http\Resources\ChannelResource;

class MessageResource extends BaseResource
{
    /**
     * Transform the resource data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function transformData($request)
    {
        return [
            'id' => (string) $this->_id,
            'content' => $this->content,
            'message_type' => $this->message_type,
            'channel_id' => (string) $this->channel_id,
            'sender_id' => (string) $this->sender_id,
            'reply_to_id' => $this->reply_to_id ? (string) $this->reply_to_id : null,
            'mentions' => $this->mentions ?? [],
            'file_id' => $this->file_id ? (string) $this->file_id : null,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'file_mime_type' => $this->file_mime_type,
            'sender' => $this->when($this->relationLoaded('sender'), new UserResource($this->sender)),
            'channel' => $this->when($this->relationLoaded('channel'), new ChannelResource($this->channel)),
            'replyTo' => $this->when($this->relationLoaded('replyTo'), new MessageResource($this->replyTo)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
