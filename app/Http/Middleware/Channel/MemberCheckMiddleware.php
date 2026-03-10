<?php

namespace App\Http\Middleware\Channel;

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $isWorkspaceMember = in_array($userId, $workspaceMemberIds, true)
            || \DB::collection('workspace_members')
                ->where('workspace_id', $workspaceId)
                ->where('user_id', $userId)
                ->exists();

        if ($type === 'direct') {
            if (!$isWorkspaceMember) {
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

        $isTeamMember = in_array($userId, $teamMemberIds, true)
            || \DB::collection('team_members')
                ->where('team_id', $teamId)
                ->where('user_id', $userId)
                ->exists();

        if (!$isWorkspaceMember || !$isTeamMember) {
            return response()->json(['error' => 'User not part of workspace or team'], 403);
        }

        return $next($request);
    }
}