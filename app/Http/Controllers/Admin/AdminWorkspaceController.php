<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkspaceReadRequest;
use App\Http\Resources\WorkspaceResource;

class AdminWorkspaceController extends Controller
{
    public function read(WorkspaceReadRequest $request)
    {
        $workspaces = $request->validatedWorkspace();

        return response()->json([
            'success' => true,
            'message' => 'Workspace(s) retrieved successfully!',
            'data' => $workspaces instanceof \Illuminate\Database\Eloquent\Collection
                ? WorkspaceResource::collection($workspaces)
                : WorkspaceResource::make($workspaces),
        ]);
    }
}
