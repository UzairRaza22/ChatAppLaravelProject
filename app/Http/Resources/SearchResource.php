<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->_id,
            'content' => $this->content,
            'message_type' => $this->message_type ?? 'text',
            'has_file' => !empty($this->file_name),
            'file_info' => $this->when(!empty($this->file_name), [
                'name' => $this->file_name,
                'mime_type' => $this->file_mime,
            ]),
            'audio_duration' => $this->audio_duration,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'sender' => $this->when($this->relationLoaded('sender') && $this->sender, [
                'id' => (string) $this->sender->_id,
                'name' => $this->sender->name,
            ]),
            'channel' => $this->when($this->relationLoaded('channel') && $this->channel, [
                'id' => (string) $this->channel->_id,
                'name' => $this->channel->name,
                'type' => $this->channel->type ?? 'channel',
            ]),
            'highlights' => $this->when(isset($this->_highlightResult), function () {
                $highlights = [];
                foreach ($this->_highlightResult as $field => $highlight) {
                    $highlights[$field] = $highlight[0] ?? '';
                }
                return $highlights;
            }),
        ];
    }
}
