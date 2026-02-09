<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTeamOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $teamId = $request->route('teamId') ?? $request->route('id');
        
        if (!$teamId) {
            abort(422, 'Team ID is required');
        }

        $user = $request->user();
        
        // Check team ownership (created_by field)
        $isOwner = $user->teams()
            ->where('_id', $teamId)
            ->where('created_by', $user->id)
            ->exists();

        if (!$isOwner) {
            abort(403, 'Only team creator can perform this action');
        }

        return $next($request);
    }
}
