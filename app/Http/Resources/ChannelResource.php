<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        // Safely get members as array regardless of how MongoDB stored it
        $rawMembers = $this->getRawOriginal('members') ?? $this->getAttribute('members') ?? [];

        // Decode if it's a JSON string, use directly if already an array
        if (is_string($rawMembers)) {
            $membersArray = json_decode($rawMembers, true) ?: [];
        } elseif (is_array($rawMembers)) {
            $membersArray = $rawMembers;
        } else {
            $membersArray = [];
        }

        $processedMembers = collect($membersArray)->map(function ($member) {
            if (is_string($member)) {
                return [
                    'user_id' => $member,
                    'role'    => 'member',
                ];
            }

            if (is_array($member)) {
                return [
                    'user_id' => $member['user_id'] ?? null,
                    'role'    => $member['role'] ?? 'member',
                ];
            }

            if (is_object($member) && isset($member->user_id)) {
                return [
                    'user_id' => (string) $member->user_id,
                    'role'    => $member->role ?? 'member',
                ];
            }

            return [
                'user_id' => null,
                'role'    => 'member',
            ];
        })->values()->toArray();

        return [
            'id'            => (string) ($this->id ?? $this->_id),
            '_id'           => (string) $this->_id,
            'name'          => $this->name,
            'workspace_id'  => (string) $this->workspace_id,
            'team_id'       => $this->team_id ? (string) $this->team_id : null,
            'type'          => $this->type,
            'direct_id'     => $this->direct_id ? (string) $this->direct_id : null,
            'created_id'    => (string) ($this->created_id ?? $this->created_by),
            'members'       => $processedMembers,
            'members_count' => count($processedMembers),
            'created_at'    => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'    => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}