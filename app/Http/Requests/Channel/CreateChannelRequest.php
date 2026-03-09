<?php

namespace App\Http\Requests\Channel;

use App\Models\Channel;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use MongoDB\BSON\ObjectId;

class CreateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user() ?? $this->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());
        $workspaceId = (string) $this->input('workspace_id');
        $teamId = (string) $this->input('team_id');
        $type = (string) $this->input('type');

        if (!$userId || !$workspaceId || !$type) {
            return false;
        }

        $workspace = Workspace::where('_id', $workspaceId)
            ->orWhere('id', $workspaceId)
            ->first();

        if (!$workspace) {
            return false;
        }

        $workspaceUserIds = $this->extractWorkspaceUserIds($workspace);

        $isWorkspaceMember = in_array($userId, $workspaceUserIds, true)
            || \DB::collection('workspace_members')
                ->where('workspace_id', $workspaceId)
                ->where('user_id', $userId)
                ->exists();

        if ($type === 'direct') {
            return $isWorkspaceMember;
        }

        if (!$teamId) {
            return false;
        }

        $team = Team::where('_id', $teamId)
            ->orWhere('id', $teamId)
            ->first();

        if (!$team || (string) $team->workspace_id !== $workspaceId) {
            return false;
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

        return $isWorkspaceMember && $isTeamMember;
    }

    public function rules(): array
    {
        $nameRule = 'required|string|unique:channels,name,NULL,id,workspace_id,' . $this->workspace_id;

        if ($this->input('type') !== 'direct') {
            $nameRule .= ',team_id,' . $this->team_id;
        }

        return [
            'name' => $nameRule,
            'workspace_id' => 'required|exists:workspaces,_id',
            'team_id' => 'nullable|required_unless:type,direct|exists:teams,_id|prohibited_if:type,direct',
            'type' => 'required|in:public,private,direct',
            'direct_user_id' => 'required_if:type,direct|exists:users,_id',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $user = $this->user() ?? $this->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());

        $data['id'] = (string) new ObjectId();
        $data['created_by'] = $userId;
        $data['created_id'] = $userId;

        if ($data['type'] !== 'direct') {
            $data['members'] = [['user_id' => $userId, 'role' => 'creator']];
            $data['join_requests'] = [];
            unset($data['direct_user_id']);

            return $data;
        }

        $directUserId = (string) $data['direct_user_id'];

        if ($directUserId === $userId) {
            abort(422, 'Direct channel requires another user');
        }

        $workspace = Workspace::where('_id', $data['workspace_id'])
            ->orWhere('id', $data['workspace_id'])
            ->first();

        if (!$workspace) {
            abort(404, 'Workspace not found');
        }

        $workspaceUserIds = $this->extractWorkspaceUserIds($workspace);

        $isDirectUserInWorkspace = in_array($directUserId, $workspaceUserIds, true)
            || \DB::collection('workspace_members')
                ->where('workspace_id', (string) $data['workspace_id'])
                ->where('user_id', $directUserId)
                ->exists();

        if (!$isDirectUserInWorkspace) {
            abort(403, 'Direct user must be part of this workspace');
        }

        $pair = collect([$userId, $directUserId])->sort()->values()->all();
        $existing = Channel::where('type', 'direct')
            ->where('workspace_id', $data['workspace_id'])
            ->whereNull('team_id')
            ->get()
            ->first(function ($channel) use ($pair) {
                $memberIds = collect($channel->members ?? [])
                    ->map(fn ($member) => (string) data_get($member, 'user_id'))
                    ->filter()
                    ->sort()
                    ->values()
                    ->all();

                return count($memberIds) === 2 && $memberIds === $pair;
            });

        if ($existing) {
            $this->attributes->set('existing_direct_channel', $existing);
        }

        $data['team_id'] = null;
        $data['direct_id'] = (string) new ObjectId();
        $data['members'] = [
            ['user_id' => $userId, 'role' => 'creator'],
            ['user_id' => $directUserId, 'role' => 'member'],
        ];
        $data['join_requests'] = [];
        unset($data['direct_user_id']);

        return $data;
    }

    private function extractWorkspaceUserIds($workspace): array
    {
        $fromMembers = collect($workspace->members ?? [])
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
            ->filter();

        $fromUserIds = collect($workspace->user_ids ?? [])
            ->map(fn ($id) => (string) $id)
            ->filter();

        return $fromMembers
            ->merge($fromUserIds)
            ->unique()
            ->values()
            ->all();
    }
}