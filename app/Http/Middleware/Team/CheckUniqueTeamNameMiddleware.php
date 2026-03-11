<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;

class CheckUniqueTeamNameMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = data_get($request, 'workspace_id');
        $teamName = data_get($request, 'name');
        
        if (!$teamName) {
            return $next($request);
        }

        $teamId = data_get($request, 'team_id') ?? $request->route('team_id');

        $query = Team::where('workspace_id', $workspaceId)
                     ->where('name', $teamName);

        if ($teamId) {
            $query->where('_id', '!=', $teamId);
        }

        if ($query->exists()) {
            return response()->error('A team with this name already exists in this workspace.');
        }

        return $next($request);
    }
}