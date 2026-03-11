<?php

namespace App\Http\Middleware\Channel;

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelAddMemberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channel = $request->attributes->get('channel');
        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        $userId = (string) $request->input('user_id');
        $authUser = $request->user() ?? $request->input('verified_user');
        $authUserId = (string) (data_get($authUser, '_id') ?? data_get($authUser, 'id') ?? auth()->id());
        $isDirect = (string) data_get($channel, 'type') === 'direct';

        if ($isDirect) {
            return response()->error('Cannot add members to a direct channel');
        }

        $isCreator = collect(data_get($channel, 'members', []))
            ->contains(fn ($member) => (string) data_get($member, 'user_id') === $authUserId && data_get($member, 'role') === 'creator');

        if ((string) data_get($channel, 'type') === 'public') {
            if ($userId !== $authUserId && !$isCreator) {
                return response()->forbidden('Only creator can add other users to a public channel');
            }
        } elseif (!$isCreator) {
            return response()->forbidden('Only creator can perform this action');
        }

        $workspace = Workspace::find(data_get($channel, 'workspace_id'));
        if (!$workspace) {
            return response()->notFound('Workspace not found');
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

        if (!in_array((string) $userId, $workspaceMemberIds, true)) {
            return response()->forbidden('User must be part of workspace to be added');
        }

        $team = Team::find(data_get($channel, 'team_id'));
        if (!$team) {
            return response()->notFound('Team not found');
        }

        if ((string) data_get($team, 'workspace_id') !== (string) data_get($channel, 'workspace_id')) {
            return response()->forbidden('Channel team does not belong to its workspace');
        }

        $teamMemberIds = collect(data_get($team, 'members', []))
            ->map(fn ($memberId) => (string) $memberId)
            ->filter()
            ->values()
            ->all();

        if (!in_array((string) $userId, $teamMemberIds, true)) {
            return response()->forbidden('User must be part of the team to be added');
        }

        $alreadyMember = collect(data_get($channel, 'members', []))->contains(
            fn ($member) => (string) data_get($member, 'user_id') === (string) $userId
        );

        if ($alreadyMember) {
            return response()->error('User is already a member of the channel');
        }

        $members = data_get($channel, 'members', []);
        $members[] = ['user_id' => $userId, 'role' => 'member'];
        $request->merge(['members' => collect($members)->values()->all()]);

        return $next($request);
    }
}