<?php

namespace App\Http\Middleware\Workspace;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUniqueWorkspaceNameMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = data_get($request, 'user');
        $userId = data_get($user, '_id');
        $workspaceName = data_get($request, 'name');

        if ($workspaceName) {
            $query = Workspace::where('creator_id', $userId)
                ->where('name', $workspaceName);

            // For update, exclude current workspace
            if ($request->has('workspace_id')) {
                $workspaceId = data_get($request, 'workspace_id');
                $query->where('_id', '!=', $workspaceId);
            }

            $exists = $query->exists();

            if ($exists) {
                return response()->validation(['name' => ['This workspace name is already taken.']], 'This workspace name is already taken.');
            }
        }

        return $next($request);
    }
}
