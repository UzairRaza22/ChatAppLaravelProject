<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use App\Http\Resources\TeamResource;
use App\Http\Resources\ChannelResource;

class WorkspaceResource extends BaseResource
{
    /**
     * Transform the resource data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function transformData($request)
    {
        return [
            'id' => (string) $this->_id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'settings' => $this->settings ?? [],
            'owner' => $this->when($this->relationLoaded('owner'), new UserResource($this->owner)),
            'members' => $this->when($this->relationLoaded('members'), UserResource::collection($this->members)),
            'teams' => $this->when($this->relationLoaded('teams'), TeamResource::collection($this->teams)),
            'channels' => $this->when($this->relationLoaded('channels'), ChannelResource::collection($this->channels)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
