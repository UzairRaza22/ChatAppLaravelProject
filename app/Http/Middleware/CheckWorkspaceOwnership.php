<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckWorkspaceOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $workspaceId = $request->route('workspaceId') ?? $request->route('id');
        
        if (!$workspaceId) {
            abort(422, 'Workspace ID is required');
        }

        $user = $request->user();
        
        // Check workspace ownership
        $isOwner = $user->ownedWorkspaces()->where('_id', $workspaceId)->exists();

        if (!$isOwner) {
            abort(403, 'Only workspace owner can perform this action');
        }

        return $next($request);
    }
}
