<?php

namespace App\Http\Middleware\Channel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\User;

class MemberCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? $request->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());
        $workspaceId = $request->workspace_id ?? $request->channel->workspace_id ?? null;
        $teamId = $request->team_id ?? $request->channel->team_id ?? null;
        $type = $request->type ?? data_get($request->channel, 'type');

        if (!$userId || !$workspaceId) {
            return response()->json(['error' => 'workspace_id is required'], 422);
        }

        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return response()->json(['error' => 'Workspace not found'], 404);
        }

        $workspaceMemberIds = collect($workspace->members ?? [])
            ->map(function ($member) {
                if ($member instanceof User) {
                    return (string) $member->_id;
                }
                if (is_array($member)) {
                    return (string) ($member['_id'] ?? $member['id'] ?? '');
                }
                if (is_object($member) && isset($member->_id)) {
                    return (string) $member->_id;
                }

                return (string) $member;
            })
            ->filter()
            ->values()
            ->all();

        if ($type === 'direct') {
            if (!in_array($userId, $workspaceMemberIds, true)) {
                return response()->json(['error' => 'User not part of workspace'], 403);
            }
            return $next($request);
        }

        if (!$teamId) {
            return response()->json(['error' => 'team_id is required for public/private channels'], 422);
        }

        $team = Team::find($teamId);
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }

        if ((string) $team->workspace_id !== (string) $workspaceId) {
            return response()->json(['error' => 'Team does not belong to the specified workspace'], 403);
        }

        $teamMemberIds = collect($team->members ?? [])
        ->map(fn ($memberId) => (string) $memberId)
        ->filter()
        ->values()
        ->all();

        if (!in_array($userId, $workspaceMemberIds, true) || !in_array($userId, $teamMemberIds, true)) {
            return response()->json(['error' => 'User not part of workspace or team'], 403);
        }
        return $next($request);
    }
}
