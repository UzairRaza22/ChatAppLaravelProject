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
        $user = $request->user();

        // Create workspace using createdWorkspaces relation to set creator_id
        $workspace = $user->createdWorkspaces()->create($request->only(['name', 'description']));

        // Attach user as member
        $workspace->members()->attach($user->id);

        return response()->json([
            'message' => 'Workspace created successfully!',
            'data' => [
                'workspace' => WorkspaceResource::make($workspace)
            ]
        ]);
    }

    public function get(Request $request)
    {
        $workspace = data_get($request, 'workspace');
        $createdWorkspaces = data_get($request, 'createdWorkspaces');
        $joinedWorkspaces = data_get($request, 'joinedWorkspaces');

        return response()->success("Workspace(s) retrieved successfully!", [
            'Workspaces' =>  $workspace ? WorkspaceResource::make($workspace) :
                [
                    'created_workspaces' => WorkspaceResource::collection($createdWorkspaces),
                    'joined_workspaces' => WorkspaceResource::collection($joinedWorkspaces)
                ]
        ]);
    }


    public function update(Request $request)
    {
        $workspace = Workspace::edit($request);


        return response()->success('Workspace updated successfully!', [
            'workspace' => WorkspaceResource::make($workspace)
        ]);
    }

    public function delete(Request $request)
    {
        $workspace = data_get($request, 'workspace');
        $workspace->teams()->delete(); // delete all teams in this workspace
        $workspace->members()->detach(); // detach all members
        $workspace->delete();

        return response()->success('Workspace deleted successfully!');
    }

    public function addMembers(Request $request)
    {

        $workspace = data_get($request, 'workspace');

        // Find users by emails
        $users = User::whereIn('email', $request->emails)->get();
        // Extract IDs, using the MongoDB _id
        $userIds = $users->pluck('_id')->toArray();

        // Sync without detaching to add new members
        $workspace->members()->syncWithoutDetaching($userIds);

        return response()->success('Members added successfully!', [
            'workspace' => WorkspaceResource::make($workspace->load('members'))
        ]);
    }

    public function removeMembers(Request $request)
    {
        $workspace = data_get($request, 'workspace');

        $users = User::whereIn('email', $request->emails)->get();
        $userIds = $users->pluck('_id')->toArray();

        $workspace->members()->detach($userIds);

        return response()->success('Members removed successfully!');
    }
}
