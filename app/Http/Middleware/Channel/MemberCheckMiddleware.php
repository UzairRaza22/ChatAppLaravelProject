<?php

namespace App\Http\Middleware\Channel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (string) ($request->user()?->_id ?? '');
        $workspaceId = $request->workspace_id ?? $request->channel->workspace_id ?? null;
        $teamId = $request->team_id ?? $request->channel->team_id ?? null;

        $workspaceMember = \DB::collection('workspace_members')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $userId)
            ->exists();

        $teamMember = \DB::collection('team_members')
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->exists();

        if (!$workspaceMember || !$teamMember) {
            return response()->json([
                'success' => false,
                'message' => 'User is not part of the channel workspace/team.',
            ], 403);
        }

        return $next($request);
    }
}
