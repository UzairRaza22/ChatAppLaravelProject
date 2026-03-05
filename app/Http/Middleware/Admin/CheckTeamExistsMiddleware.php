<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = $request->route('team_id');

        if ($teamId && !Team::find($teamId)) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found.'
            ], 404);
        }

        return $next($request);
    }
}
