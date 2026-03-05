<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->_id,
            'name' => $this->name,
            'team_id' => $this->team_id,
            'creator_id' => $this->creator?->_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
