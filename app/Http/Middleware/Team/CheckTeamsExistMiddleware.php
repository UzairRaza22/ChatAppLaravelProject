<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamsExistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = data_get($request, 'workspace_id');

        // Teams fetch karna
        $teams = Team::where('workspace_id', $workspaceId)->get();

        if ($teams->isEmpty()) {
            abort(404, 'No teams found for this workspace.');
        }

        $request->merge(['teams' => $teams]);

        return $next($request);
    }
}