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

        return response()->success([
            'workspace' => WorkspaceResource::make($workspace)
        ], 'Workspace created successfully!');
    }

    public function read(Request $request, $id = null)
    {
        $user = auth()->user();
        
        $workspaces = Workspace::where('creator_id', $user->_id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->_id);
            })
            ->when($id, function ($query) use ($id) {
                $query->where('_id', $id);
            })
            ->get();

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

        // Find users by emails
        $users = User::whereIn('email', $request->emails)->get();
        // Extract IDs, using the MongoDB _id
        $userIds = $users->pluck('_id')->toArray();

        // Sync without detaching to add new members
        $workspace->members()->syncWithoutDetaching($userIds);

        return response()->success([
            'workspace' => WorkspaceResource::make($workspace->load('members'))
        ], 'Members added successfully!');
    }

    public function removeMembers(Request $request)
    {
        $workspace = data_get($request, 'workspace');

        $users = User::whereIn('email', $request->emails)->get();
        $userIds = $users->pluck('_id')->toArray();

        $workspace->members()->detach($userIds);

        return response()->success(null, 'Members removed successfully!');
    }
}
