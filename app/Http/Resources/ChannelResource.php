<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        // Handle members field with multiple fallback methods
        $members = [];
        
        // Try raw attributes first (for MongoDB JSON strings)
        $rawMembers = $this->getRawOriginal('members');
        if ($rawMembers && is_string($rawMembers)) {
            $decodedMembers = json_decode($rawMembers, true) ?: [];
            $members = collect($decodedMembers);
        } else {
            // Fallback to attribute access
            $members = collect($this->getAttribute('members') ?? []);
        }
        
        // Process members into consistent format
        $processedMembers = $members->map(function ($member) {
            // Handle string members
            if (is_string($member)) {
                return [
                    'user_id' => $member,
                    'role'    => 'member',
                ];
            }
            // Handle object members
            elseif (is_array($member)) {
                return [
                    'user_id' => $member['user_id'] ?? null,
                    'role'    => $member['role'] ?? 'member',
                ];
            }
            // Handle full objects with user_id property
            elseif (is_object($member) && isset($member->user_id)) {
                return [
                    'user_id' => (string) $member->user_id,
                    'role'    => $member['role'] ?? 'member',
                    'name'    => $member['name'] ?? null,
                    'email'   => $member['email'] ?? null,
                ];
            }
            // Default fallback
            else {
                return [
                    'user_id' => null,
                    'role'    => 'member',
                ];
            }
        })
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

            'members'       => $processedMembers,
            'members_count' => count($processedMembers),
            'created_at'    => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'    => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}