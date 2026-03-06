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
        $incomingMemberIds = collect($request->member_ids ?? [])
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        $existingMemberIds = collect($team->members ?? [])
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        foreach ($incomingMemberIds as $memberId) {
            if (in_array($memberId, $existingMemberIds, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this team.'
                ], 409);
            }
        }

        return $next($request);
    }
}
