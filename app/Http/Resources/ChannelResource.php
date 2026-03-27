<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        $members = collect($this->members ?? [])
            ->map(function ($member) {
                return [
                    'user_id' => (string) $member['user_id'],
                    'role' => $member['role'] ?? 'member'
                ];
            })
            ->values()
            ->toArray();

        return [
            'id' => (string) ($this->id ?? $this->_id),
            '_id' => (string) $this->_id,
            'name' => $this->name,
            'workspace_id' => (string) $this->workspace_id,
            'team_id' => $this->team_id ? (string) $this->team_id : null,
            'type' => $this->type,
            'direct_id' => $this->direct_id ? (string) $this->direct_id : null,
            'created_id' => (string) ($this->created_id ?? $this->created_by),

            'members_count' => count($members),
            'members' => $members,

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}