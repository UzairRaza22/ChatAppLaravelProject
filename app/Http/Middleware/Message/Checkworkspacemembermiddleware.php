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
            return response()->notFound('Workspace not found.');
        }
        $isMember = $workspace->members()
            ->get()
            ->contains('_id', $user->_id);

        if (!$isMember) {
            return response()->forbidden('You are not a member of this workspace.');
        }

        $request->merge(['workspace' => $workspace]);

        return $next($request);
    }
}
