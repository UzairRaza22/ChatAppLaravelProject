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
        $workspaceId = $request->workspace_id;

        $teams = Team::where('workspace_id', $workspaceId)->get();

        if ($teams->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No teams found for this workspace.'
            ], 404);
        }

        // Teams ko request mein save kar rahe hain taake controller mein dobara fetch na karni parain
        $request->attributes->set('teams', $teams);

        return $next($request);
    }
}