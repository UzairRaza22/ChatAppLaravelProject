<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        // Debug the raw members data
        \Log::info('ChannelResource - Raw members for channel ' . $this->name . ': ' . json_encode($this->members));
        
        $members = collect($this->members ?? [])
            ->map(function ($member) {
                \Log::info('Processing member: ' . json_encode($member));
                
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
                        \Log::info('Found user_id structure: ' . $userId . ' with role: ' . $role);
                    } elseif (isset($member['id'])) {
                        // Full user object: {"id": "...", "name": "...", ...}
                        $userId = $member['id'];
                        $role = $member['role'] ?? 'member';
                        \Log::info('Found id structure: ' . $userId . ' with role: ' . $role);
                    }
                    
                    $result = [
                        'user_id' => $userId,
                        'role'    => $role,
                        'name'    => $member['name'] ?? null, // Include name if available
                        'email'   => $member['email'] ?? null, // Include email if available
                    ];
                    
                    \Log::info('Returning member: ' . json_encode($result));
                    return $result;
                }
                \Log::info('Member is neither string nor array, returning null');
                return null;
            })
            ->filter() // Remove null values
            ->values()
            ->toArray();

        \Log::info('Final processed members: ' . json_encode($members));

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