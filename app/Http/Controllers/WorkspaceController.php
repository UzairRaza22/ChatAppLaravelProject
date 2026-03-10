<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Http\Resources\WorkspaceResource;
use App\Models\User;

class WorkspaceController extends Controller
{
    public function create(Request $request)
    {
        $user = data_get($request, 'user');

        // Create workspace using createdWorkspaces relation to set creator_id
        $workspace = $user->createdWorkspaces()->create($request->only(['name', 'description']));

        // Attach user as member
        $workspace->members()->attach(data_get($user, 'id'));

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

        return response()->success([
            'workspace' => WorkspaceResource::make($workspace)
        ], 'Workspace updated successfully!');
    }

    public function delete(Request $request)
    {
        $workspace = data_get($request, 'workspace');
        $workspace->members()->detach(); // detach all members
        $workspace->delete();

        return response()->success(null, 'Workspace deleted successfully!');
    }

    public function addMembers(Request $request)
    {
        $workspace = data_get($request, 'workspace');

        // Get user IDs from request
        $userIds = data_get($request, 'user_ids');

        // Sync without detaching to add new members
        $workspace->members()->syncWithoutDetaching($userIds);

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

        return response()->success(null, 'Members removed successfully!');
    }
}
