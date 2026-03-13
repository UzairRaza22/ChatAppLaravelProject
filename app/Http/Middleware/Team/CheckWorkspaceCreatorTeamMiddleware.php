<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceCreatorTeamMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); 

        if (!$user) {
            abort(401, 'Unauthenticated. Please login to continue.');
        }
     $team = data_get($request, 'team'); // Pehle variable ko pakrein (chahay wo null hi kyun na ho)
     $workspaceId = $team ? $team->workspace_id : data_get($request, 'workspace_id');

        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            abort(404, 'Workspace not found.');
        }

        $creatorIdRaw = data_get($workspace, 'creator_id');
        $creatorId = (string) (data_get($creatorIdRaw, '$oid') ?? $creatorIdRaw);

        $userIdRaw = data_get($user, '_id');
        $userId = (string) (data_get($userIdRaw, '$oid') ?? $userIdRaw);

        // Creator ID check
        if ($creatorId !== $userId) {
            abort(403, 'Unauthorized access: You are not the creator of this workspace.');
        }

        $request->merge([
            'workspace' => $workspace,
        ]);

        return $next($request);
    }
}