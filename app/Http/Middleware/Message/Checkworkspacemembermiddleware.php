<?php

namespace App\Http\Middleware\Message;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceMemberMiddleware
{
    /**
     * Ensure the authenticated user is a member of the given workspace.
     * Resolves the workspace from 'workspace_id' input and merges it into request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user        = $request->user();
        $workspaceId = $request->input('workspace_id');

        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            return response()->json([
                'message' => 'Workspace not found.'
            ], 404);
        }

        $isMember = $workspace->members()
            ->where('_id', $user->_id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'message' => 'You are not a member of this workspace.'
            ], 403);
        }

        $request->merge(['workspace' => $workspace]);

        return $next($request);
    }
}
