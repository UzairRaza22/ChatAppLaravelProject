<?php

namespace App\Http\Middleware\Channel;

use App\Models\Channel;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response;

class ChannelCreateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? $request->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());
        $workspaceId = (string) $request->input('workspace_id');
        $teamId = (string) $request->input('team_id');
        $type = (string) $request->input('type');

        if (!$userId || !$workspaceId || !$type) {
            return response()->error('user_id, workspace_id, and type are required');
        }

        $workspace = Workspace::where('_id', $workspaceId)
            ->orWhere('id', $workspaceId)
            ->first();

        if (!$workspace) {
            return response()->notFound('Workspace not found.');
        }

        $workspaceUserIds = $this->extractWorkspaceUserIds($workspace);

        $isWorkspaceMember = in_array($userId, $workspaceUserIds, true)
            || \DB::collection('workspace_members')
                ->where('workspace_id', $workspaceId)
                ->where('user_id', $userId)
                ->exists();

        if ($type === 'direct' && !$isWorkspaceMember) {
            return response()->forbidden('User is not a member of workspace');
        }

        if ($type !== 'direct') {
            if (!$teamId) {
                return response()->error('team_id is required for public/private channels');
            }

            $team = Team::where('_id', $teamId)
                ->orWhere('id', $teamId)
                ->first();

            if (!$team || (string) data_get($team, 'workspace_id') !== $workspaceId) {
                return response()->forbidden('This action is unauthorized.');
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
                return response()->forbidden('This action is unauthorized.');
            }
        }

        $data = $request->all();
        $data['id'] = (string) new ObjectId();
        $data['created_by'] = $userId;
        $data['created_id'] = $userId;

        if ($type !== 'direct') {
            $data['members'] = [['user_id' => $userId, 'role' => 'creator']];
            unset($data['direct_user_id']);
            $request->request->remove('direct_user_id');
            $request->merge($data);

            return $next($request);
        }

        $directUserId = (string) $request->input('direct_user_id');
        if (!$directUserId) {
            return response()->error('Direct channel requires another user');
        }

        if ($directUserId === $userId) {
            return response()->error('Direct channel requires another user');
        }

        $isDirectUserInWorkspace = in_array($directUserId, $workspaceUserIds, true)
            || \DB::collection('workspace_members')
                ->where('workspace_id', $workspaceId)
                ->where('user_id', $directUserId)
                ->exists();

        if (!$isDirectUserInWorkspace) {
            return response()->forbidden('Direct user must be part of this workspace');
        }

        $pair = collect([$userId, $directUserId])->sort()->values()->all();
        $workspaceObjectId = null;
        try {
            $workspaceObjectId = new ObjectId($workspaceId);
        } catch (\Throwable $e) {
            $workspaceObjectId = null;
        }

        $existingCandidates = Channel::where('type', 'direct')
            ->whereNull('team_id')
            ->where(function ($query) use ($workspaceId, $workspaceObjectId) {
                $query->where('workspace_id', $workspaceId);
                if ($workspaceObjectId) {
                    $query->orWhere('workspace_id', $workspaceObjectId);
                }
            })
            ->get();

        $existing = $existingCandidates->first(function ($channel) use ($pair) {
            $memberIds = collect(data_get($channel, 'members', []))
                ->map(fn ($member) => (string) data_get($member, 'user_id'))
                ->filter()
                ->sort()
                ->values()
                ->all();

            return count($memberIds) === 2 && $memberIds === $pair;
        });

        if ($existing) {
            return response()->error('Channel already exists');
        }

        $data['team_id'] = null;
        $data['direct_id'] = (string) new ObjectId();
        $data['members'] = [
            ['user_id' => $userId, 'role' => 'creator'],
            ['user_id' => $directUserId, 'role' => 'member'],
        ];
        unset($data['direct_user_id']);
        $request->request->remove('direct_user_id');
        $request->merge($data);

        return $next($request);
    }

    private function extractWorkspaceUserIds($workspace): array
    {
        $fromMembers = collect(data_get($workspace, 'members', []))
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
            ->filter();

        $fromUserIds = collect(data_get($workspace, 'user_ids', []))
            ->map(fn ($id) => (string) $id)
            ->filter();

        return $fromMembers
            ->merge($fromUserIds)
            ->unique()
            ->values()
            ->all();
    }
}
