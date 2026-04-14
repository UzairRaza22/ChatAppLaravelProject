<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Http\Resources\WorkspaceResource;
use App\Models\User;
use Illuminate\Support\Facades\Http;
class WorkspaceController extends Controller
{
     private function sendEvent($event)
    {
        Http::post('http://localhost:3000/event', $event);
    }
    public function create(Request $request)
    {
        $user = data_get($request, 'user');

        // Create workspace using createdWorkspaces relation to set creator_id
        $workspace = $user->createdWorkspaces()->create($request->only(['name', 'description']));

        // Attach user as member
        $workspace->members()->attach(data_get($user, 'id'));

        // eent
        $event = [
            'eventName' => 'workspace_created',
            'module' => 'workspace',
            'operation' => 'create',
            'referenceId' => $workspace->_id ?? $workspace->id,
            'userIds' => $workspace->members()->pluck('user_id')->toArray(),
            'metadata' => [
                'workspace' => WorkspaceResource::make($workspace)
            ]
        ];

        $this->sendEvent($event);

        return response()->success([
            'workspace' => WorkspaceResource::make($workspace)
        ], 'Workspace created successfully!');
    }

    public function read(Request $request, $id = null)
    {
        $workspaces = data_get($request, 'workspaces', collect());

        return response()->success(
            WorkspaceResource::collection($workspaces),
            "Workspace(s) retrieved successfully!"
        );
    }


    public function update(Request $request)
    {
        $workspace = Workspace::edit($request);
 // eent
        $event = [
            'eventName' => 'workspace_updated',
            'module' => 'workspace',
            'operation' => 'update',
            'referenceId' => $workspace->_id ?? $workspace->id,
            'userIds' => $workspace->members()->pluck('user_id')->toArray(),
            'metadata' => [
                'workspace' => WorkspaceResource::make($workspace)
            ]
        ];

        $this->sendEvent($event);

        return response()->success([
            'workspace' => WorkspaceResource::make($workspace)
        ], 'Workspace updated successfully!');
    }

    public function delete(Request $request)
    {
        $workspace = data_get($request, 'workspace');
        $memberIds = $workspace->members()
            ->pluck('user_id')
            ->toArray();
        $workspace->members()->detach(); // detach all members
        $workspace->delete();
        // eent
         $event = [
        'eventName' => 'workspace_deleted',
        'module' => 'workspace',
        'operation' => 'delete',
        'referenceId' => $workspace->_id ?? $workspace->id,
        'userIds' => $memberIds,
        'metadata' => [
            'workspaceId' => (string) ($workspace->_id ?? $workspace->id)
        ]
    ];

    $this->sendEvent($event);
        return response()->success(null, 'Workspace deleted successfully!');
    }

    public function addMembers(Request $request)
    {
        $workspace = data_get($request, 'workspace');

        // Get user IDs from request
        $userIds = data_get($request, 'user_ids');

        // Sync without detaching to add new members
        $workspace->members()->syncWithoutDetaching($userIds);
    //eent
    $event = [
        'eventName' => 'workspace_member_added',
        'module' => 'workspace',
        'operation' => 'member_added',
        'referenceId' => $workspace->_id ?? $workspace->id,
        'userIds' => $workspace->members()->pluck('user_id')->toArray(),
        'metadata' => [
            'workspace' => WorkspaceResource::make($workspace),
            'workspaceId' => (string) ($workspace->_id ?? $workspace->id),
            'addedUserIds' => $userIds
        ]
    ];

    $this->sendEvent($event);

        return response()->success([
            'workspace' => WorkspaceResource::make($workspace->load('members'))
        ], 'Members added successfully!');
    }

    public function removeMembers(Request $request)
    {
        $workspace = data_get($request, 'workspace');

        // Get user IDs from request
        $userIds = data_get($request, 'user_ids');

        // Detach specified members
        $workspace->members()->detach($userIds);
//eent
    $event = [
        'eventName' => 'workspace_member_removed',
        'module' => 'workspace',
        'operation' => 'member_removed',
        'referenceId' => $workspace->_id ?? $workspace->id,
        'userIds' => $workspace->members()->pluck('user_id')->toArray(),
        'metadata' => [
            'workspaceId' => (string) ($workspace->_id ?? $workspace->id),
            'removedUserIds' => $userIds
        ]
    ];

    $this->sendEvent($event);

        return response()->success(null, 'Members removed successfully!');
    }
}
