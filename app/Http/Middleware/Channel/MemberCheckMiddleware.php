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
        $workspaceId = data_get($request, 'workspace_id') ?? data_get($request, 'channel.workspace_id');
        $teamId = data_get($request, 'team_id') ?? data_get($request, 'channel.team_id');
        $type = data_get($request, 'type') ?? data_get($request, 'channel.type');

        if (!$userId || !$workspaceId) {
            return response()->error('workspace_id is required');
        }

        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return response()->notFound('Workspace not found.');
        }

        $workspaceMemberIds = collect(data_get($workspace, 'members', []))
            ->map(function ($member) {
                if ($member instanceof User) {
                    return (string) (data_get($member, '_id') ?? data_get($member, 'id'));
                }
                if (is_array($member)) {
                    return (string) ($member['_id'] ?? $member['id'] ?? '');
                }
                if (is_object($member) && (data_get($member, '_id') || data_get($member, 'id'))) {
                    return (string) (data_get($member, '_id') ?? data_get($member, 'id'));
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
                return response()->forbidden('User not part of workspace');
            }

            return $next($request);
        }

        if (!$teamId) {
            return response()->error('team_id is required for public/private channels');
        }

        $team = Team::find($teamId);
        if (!$team) {
            return response()->notFound('Team not found.');
        }

        if ((string) data_get($team, 'workspace_id') !== (string) $workspaceId) {
            return response()->forbidden('Team does not belong to the specified workspace');
        }

        $teamMemberIds = collect(data_get($team, 'members', []))
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
            return response()->forbidden('User not part of workspace or team');
        }

        return $next($request);
    }
}