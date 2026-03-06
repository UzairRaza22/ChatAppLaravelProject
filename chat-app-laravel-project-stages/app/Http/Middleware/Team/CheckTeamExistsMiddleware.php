<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = $request->team_id;

        $team = Team::find($teamId);

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found.'
            ], 404);
        }

        // Team object ko request mein save kar dein taake controller mein query na karni paray
        $request->attributes->set('team', $team);

        return $next($request);
    }
}