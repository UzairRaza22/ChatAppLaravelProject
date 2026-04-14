<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;
use MongoDB\BSON\ObjectId;

class CheckWorkspaceExistsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try to get workspace_id from multiple sources:
        // 1. From request body/query (POST/DELETE endpoints)
        // 2. From route parameter {id} (GET endpoints)
        $workspaceId = data_get($request, 'workspace_id')
                    ?? $request->route('id')
                    ?? $request->query('workspace_id');

        if (!$workspaceId) {
            return response()->notFound('Workspace ID is required.');
        }

        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            return response()->notFound('Workspace not found.');
        }

        // Store workspace in request for use in controller
        $request->merge(['workspace' => $workspace]);

        return $next($request);
    }
}
