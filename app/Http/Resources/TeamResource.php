<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id, 
            'name'         => $this->name,
            'description'  => $this->description ?? '',
            'workspace_id' => (string) $this->workspace_id,
            'creator_id'   => (string) $this->creator_id,
            
            // Members ki count aur list
            'members_count' => is_array($this->members) ? count($this->members) : 0,
            'members'      => $this->members ?? [],
            
            // Timestamps format
            'created_at'   => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at'   => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}