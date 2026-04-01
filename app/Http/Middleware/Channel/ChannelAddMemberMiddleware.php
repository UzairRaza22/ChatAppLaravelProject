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

        $userIds = $request->input('user_ids');
        $userIds = is_array($userIds) && count($userIds) ? $userIds : [(string) $request->input('user_id')];
        $userIds = collect($userIds)->map(fn ($id) => (string) $id)->filter()->values()->all();
        $request->merge(['user_ids' => $userIds]);

        $userId = (string) data_get($userIds, 0);
        $authUser = $request->user() ?? $request->input('verified_user');
        $authUserId = (string) (data_get($authUser, '_id') ?? data_get($authUser, 'id') ?? auth()->id());
        $isDirect = (string) data_get($channel, 'type') === 'direct';
        $isPublic = (string) data_get($channel, 'type') === 'public';
        $isSelfJoin = collect($userIds)->every(fn ($id) => (string) $id === $authUserId);

        if ($isDirect) {
            return response()->error('Cannot add members to a direct channel');
        }

        $isCreator = (string) data_get($channel, 'created_id') === $authUserId;

        if ($isPublic) {
            if (!$isSelfJoin && !$isCreator) {
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

        if (!$isPublic || !$isSelfJoin) {
            $missingWorkspace = collect($userIds)
                ->reject(fn ($id) => in_array((string) $id, $workspaceMemberIds, true))
                ->values()
                ->all();
            if (count($missingWorkspace)) {
                return response()->forbidden('User must be part of workspace to be added');
            }
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

        $missingTeam = collect($userIds)
            ->reject(fn ($id) => in_array((string) $id, $teamMemberIds, true))
            ->values()
            ->all();
        if (count($missingTeam)) {
            return response()->forbidden('User must be part of the team to be added');
        }

        $channelMemberIds = collect(data_get($channel, 'members', []))
            ->map(function ($member) {
                if (is_array($member)) {
                    return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '');
                }
                if (is_object($member)) {
                    return (string) (data_get($member, 'user_id') ?? data_get($member, '_id') ?? data_get($member, 'id'));
                }

                return (string) $member;
            })
            ->filter()
            ->values()
            ->all();

        $alreadyMember = collect($userIds)->contains(fn ($id) => in_array((string) $id, $channelMemberIds, true));

        if ($alreadyMember) {
            return response()->error('User is already a member of the channel');
        }

        $channelMemberIds = array_merge($channelMemberIds, $userIds);
        $request->merge(['members' => collect($channelMemberIds)->unique()->values()->all()]);

        return $next($request);
    }
}
