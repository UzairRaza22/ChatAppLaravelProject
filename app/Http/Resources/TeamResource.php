<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use App\Http\Resources\ChannelResource;
use App\Http\Resources\WorkspaceResource;

class TeamResource extends BaseResource
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
            'workspace_id' => (string) $this->workspace_id,
            'is_active' => $this->is_active,
            'settings' => $this->settings ?? [],
            'members' => $this->when($this->relationLoaded('members'), UserResource::collection($this->members)),
            'channels' => $this->when($this->relationLoaded('channels'), ChannelResource::collection($this->channels)),
            'workspace' => $this->when($this->relationLoaded('workspace'), new WorkspaceResource($this->workspace)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
