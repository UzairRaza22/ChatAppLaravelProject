<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string) ($this->id ?? $this->_id),
            '_id' => (string) $this->_id,
            'name' => $this->name,
            'workspace_id' => (string) $this->workspace_id,
            'team_id' => $this->team_id ? (string) $this->team_id : null,
            'type' => $this->type,
            'direct_id' => $this->direct_id ? (string) $this->direct_id : null,
            'created_id' => $this->created_id ? (string) $this->created_id : (string) $this->created_by,
            'members' => $this->members ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
