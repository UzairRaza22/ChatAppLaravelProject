<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\Channel;
use App\Models\User;

class CreateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user() ?? $this->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());
        $workspaceId = $this->input('workspace_id');
        $teamId = $this->input('team_id');
        $type = $this->input('type');

        if (!$userId || !$workspaceId || !$type) {
            return false;
        }

        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return false;
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
            return in_array($userId, $workspaceMemberIds, true);
        }

        if (!$teamId) {
            return false;
        }

        $team = Team::find($teamId);
        if (!$team || (string) $team->workspace_id !== (string) $workspaceId) {
            return false;
        }

        $teamMemberIds = collect($team->members ?? [])
        ->map(fn ($memberId) => (string) $memberId)
        ->filter()
        ->values()
        ->all();

        return in_array($userId, $workspaceMemberIds, true)
            && in_array($userId, $teamMemberIds, true);
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
            'direct_user_id' => 'required_if:type,direct|exists:users,_id|different:created_by',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $user = $this->user() ?? $this->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());

        $data['created_by'] = $userId;

        if ($data['type'] !== 'direct') {
            $data['members'] = [['user_id' => $userId, 'role' => 'admin']];
            unset($data['direct_user_id']);
            return $data;
        }

        $directUserId = (string) $data['direct_user_id'];
        $workspace = Workspace::find($data['workspace_id']);
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
            ->all();

        if (!in_array($directUserId, $workspaceMemberIds, true)) {
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
        $data['members'] = [
            ['user_id' => $userId, 'role' => 'admin'],
            ['user_id' => $directUserId, 'role' => 'member'],
        ];

        unset($data['direct_user_id']);

        return $data;
    }
}
