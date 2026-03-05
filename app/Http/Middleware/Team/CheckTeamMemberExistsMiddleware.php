<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamMemberExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $team = $request->attributes->get('team'); // Jo CheckTeamExists ne fetch kiya tha
        $memberId = $request->member_id;

        if (in_array($memberId, $team->members ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this team.'
            ], 409);
        }

        return $next($request);
    }
}