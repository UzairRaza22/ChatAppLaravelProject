<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;
use MongoDB\BSON\ObjectId;

class CheckWorkspacesExistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = $request->input('workspace_id');

        if ($workspaceId) {
            $workspace = Workspace::where('_id', $workspaceId)->first();

            if (!$workspace) {
                return response()->notFound('Workspace not found.');
            }

            $request->merge(['workspace' => $workspace]);
        } else {
            // Fetch all workspaces of the logged-in user
            $user = $request->user();
            if ($user) {
                // Get all workspaces user has created
                $createdWorkspaces = $user->createdWorkspaces()->get();

                // Get all workspaces user is a member of
                $joinedWorkspaces = $user->workspaces()->get();

                $request->merge([
                    'createdWorkspaces' => $createdWorkspaces,
                    'joinedWorkspaces' => $joinedWorkspaces,
                ]);
            }
        }

        return $next($request);
    }
}
