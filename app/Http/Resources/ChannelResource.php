<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use App\Http\Resources\TeamResource;
use App\Http\Resources\WorkspaceResource;

class ChannelResource extends BaseResource
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
            'type' => $this->type,
            'workspace_id' => (string) $this->workspace_id,
            'team_id' => (string) $this->team_id,
            'is_active' => $this->is_active,
            'settings' => $this->settings ?? [],
            'creator' => $this->when($this->relationLoaded('creator'), new UserResource($this->creator)),
            'members' => $this->when($this->relationLoaded('members'), UserResource::collection($this->members)),
            'workspace' => $this->when($this->relationLoaded('workspace'), new WorkspaceResource($this->workspace)),
            'team' => $this->when($this->relationLoaded('team'), new TeamResource($this->team)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
