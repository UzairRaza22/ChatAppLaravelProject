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
            return response()->error('workspace_id is required', 422);
        }

        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return response()->notFound('Workspace not found.');
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
                return response()->forbidden('User not part of workspace');
            }
            return $next($request);
        }

        if (!$teamId) {
            return response()->error('team_id is required for public/private channels', 422);
        }

        $team = Team::find($teamId);
        if (!$team) {
            return response()->notFound('Team not found.');
        }

        if ((string) $team->workspace_id !== (string) $workspaceId) {
            return response()->forbidden('Team does not belong to the specified workspace');
        }

        $teamMemberIds = collect($team->members ?? [])
        ->map(fn ($memberId) => (string) $memberId)
        ->filter()
        ->values()
        ->all();

        if (!in_array($userId, $workspaceMemberIds, true) || !in_array($userId, $teamMemberIds, true)) {
            return response()->forbidden('User not part of workspace or team');
        }
        return $next($request);
    }
}
