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
        $teamId = data_get($request, 'team_id') ?? $request->route('team_id');

        $team = Team::find($teamId);

        if (!$team) {
            abort(404, 'Team not found.');
        }

        $request->merge(['team' => $team]);

        return $next($request);
    }
}