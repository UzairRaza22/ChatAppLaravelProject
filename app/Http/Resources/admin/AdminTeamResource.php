<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->_id,
            'name' => $this->name,
            'workspace_id' => $this->workspace_id,
            'creator_id' => $this->creator?->_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
