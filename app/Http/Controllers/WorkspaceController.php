<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use App\Models\User;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of workspaces.
     */
    public function readAll(Request $request)
    {
        $workspaces = Workspace::with(['owner', 'members'])
            ->where('owner_id', $request->user()->_id)
            ->paginate(20);

        return WorkspaceResource::collection($workspaces);
    }

    /**
     * Store a newly created workspace.
     */
    public function create(CreateWorkspaceRequest $request)
    {
        $workspace = Workspace::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $request->user()->id,
        ]);

        // Add owner as a member
        $workspace->members()->attach($request->user()->id, ['role' => 'owner']);

        return new WorkspaceResource($workspace);
    }

    /**
     * Display the specified workspace.
     */
    public function read(Request $request, $id)
    {
        $workspace = $request->user()->workspaces()
            ->with(['owner', 'members'])
            ->findOrFail($id);

        return new WorkspaceResource($workspace);
    }

    /**
     * Update the specified workspace.
     */
    public function update(UpdateWorkspaceRequest $request, $id)
    {
        $workspace = $request->user()->workspaces()
            ->findOrFail($id);

        $workspace->update($request->validated());

        return new WorkspaceResource($workspace);
    }

    /**
     * Remove the specified workspace.
     */
    public function delete(Request $request, $id)
    {
        $workspace = $request->user()->workspaces()->findOrFail($id);
        $workspace->delete();

        return new SuccessResource(['message' => 'Workspace deleted successfully']);
    }
}
