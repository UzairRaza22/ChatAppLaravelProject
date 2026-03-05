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
        $workspaceId = $request->workspace_id;
        $teamName = $request->name;

        $exists = Team::where('workspace_id', $workspaceId)
                      ->where('name', $teamName)
                      ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A team with this name already exists in this workspace.'
            ], 409);
        }

        return $next($request);
    }
}