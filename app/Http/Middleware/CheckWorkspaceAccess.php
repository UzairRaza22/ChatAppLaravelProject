<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;

class CheckWorkspaceAccess
{
    public function handle(Request $request, Closure $next)
    {
        $workspaceId = $request->route('workspaceId') ?? $request->route('id');
        
        if (!$workspaceId) {
            abort(422, 'Workspace ID is required');
        }

        $user = $request->user();
        
        $hasAccess = $user->workspaces()->where('_id', $workspaceId)->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to this workspace');
        }

        return $next($request);
    }
}
