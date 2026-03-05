<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (string)$this->_id,
            'name' => $this->name,
            'workspace_id' => (string)$this->workspace_id,
            'team_id' => (string)$this->team_id,
            'type' => $this->type,
            'members' => $this->members ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}