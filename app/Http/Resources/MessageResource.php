<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'workspace_id' => $this->workspace_id,
            'sender_id'    => $this->sender_id,
            'receiver_id'  => $this->receiver_id,
            'channel_id'   => $this->channel_id,
            'message_type' => $this->message_type,
            'content'      => $this->content,
            'file_path'    => $this->file_path,
            'file_name'    => $this->file_name,
            'file_mime'    => $this->file_mime,
            'file_download_url' => $this->file_path
                ? url('api/messages/download?path=' . urlencode($this->file_path))
                : null,
            'sender'   => $this->whenLoaded(
                'sender',
                fn() => UserResource::make($this->sender)
            ),
            'receiver' => $this->whenLoaded(
                'receiver',
                fn() => $this->receiver ? UserResource::make($this->receiver) : null
            ),
            'channel'  => $this->whenLoaded(
                'channel',
                fn() => $this->channel
                    ? ['id' => $this->channel->id, 'name' => $this->channel->name]
                    : null
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // ── Read Receipts ─────────────────────────────────────────────────
            'read_by_count'  => count($this->read_by ?? []),
            'is_read_by_me'  => in_array(
                (string) auth()->id(),
                array_map('strval', $this->read_by ?? [])
            ),

            // ── Reactions ─────────────────────────────────────────────────────
            'reactions_summary' => collect($this->reactions ?? [])
                ->map(function ($users, $emoji) {
                    $me          = (string) auth()->id();
                    $userIds     = array_map('strval', (array) $users);
                    return [
                        'emoji'          => $emoji,
                        'count'          => count($userIds),
                        'reacted_by_me'  => in_array($me, $userIds),
                        'reacted_by'     => $userIds,   // full list of user IDs who reacted
                    ];
                })
                ->values()
                ->toArray(),
        ];
    }
}
