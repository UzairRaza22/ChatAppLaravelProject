<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTeamAccess
{
    public function handle(Request $request, Closure $next)
    {
        $teamId = $request->route('teamId') ?? $request->route('id');
        
        if (!$teamId) {
            abort(422, 'Team ID is required');
        }

        $user = $request->user();
        
        // Check team access
        $hasAccess = $user->teams()->where('_id', $teamId)->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to this team');
        }

        return $next($request);
    }
}
