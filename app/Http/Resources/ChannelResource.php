<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        // Get members safely using getAttribute to avoid MongoDB query issues
        $membersData = $this->getAttribute('members');
            ->map(function ($member) {
                if (is_string($member)) {
                    return [
                        'user_id' => $member,
                        'role'    => 'member',
                    ];
                } elseif (is_array($member)) {
                    // Handle different member structures
                    $userId = null;
                    $role = 'member';
                    
                    if (isset($member['user_id'])) {
                        // Standard structure: {"user_id": "...", "role": "..."}
                        $userId = $member['user_id'];
                        $role = $member['role'] ?? 'member';
                    } elseif (isset($member['id'])) {
                        // Full user object: {"id": "...", "name": "...", ...}
                        $userId = $member['id'];
                        $role = $member['role'] ?? 'member';
                    }
                    
                    return [
                        'user_id' => $userId,
                        'role'    => $role,
                        'name'    => $member['name'] ?? null,
                        'email'   => $member['email'] ?? null,
                    ];
                }
                return null;
            })
            ->filter()
            ->values()
            ->toArray();

        return [
            'id'            => (string) ($this->id ?? $this->_id),
            '_id'           => (string) $this->_id,
            'name'          => $this->name,
            'workspace_id'  => (string) $this->workspace_id,
            'team_id'       => $this->team_id ? (string) $this->team_id : null,
            'type'          => $this->type,
            'direct_id'     => $this->direct_id ? (string) $this->direct_id : null,
            'created_id'    => (string) ($this->created_id ?? $this->created_by),
            'members'       => $members,
            'members_count' => count($members),
            'created_at'    => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'    => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}