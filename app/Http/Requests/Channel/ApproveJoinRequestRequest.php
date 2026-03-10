<?php

namespace App\Http\Requests\Channel;

use App\Models\Channel;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class ApproveJoinRequestRequest extends FormRequest
{
    protected $channel;

    public function authorize(): bool
    {
        $this->channel = $this->attributes->get('channel');

        if (!$this->channel && $this->route('id')) {
            $this->channel = Channel::where('_id', $this->route('id'))->orWhere('id', $this->route('id'))->first();
        }

        return $this->channel !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,_id',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $channel = $this->channel;
        $userId = (string) $data['user_id'];

        if ((string) $channel->type !== 'public') {
            abort(403, 'Only public channels use join requests');
        }

        $joinRequests = collect($channel->join_requests ?? []);

        $hasPending = $joinRequests->contains(function ($joinRequest) use ($userId) {
            return (string) data_get($joinRequest, 'user_id') === $userId
                && (string) data_get($joinRequest, 'status') === 'pending';
        });

        if (!$hasPending) {
            abort(400, 'No pending join request for this user');
        }

        $workspace = Workspace::where('_id', $channel->workspace_id)
            ->orWhere('id', $channel->workspace_id)
            ->first();

        if (!$workspace) {
            abort(404, 'Workspace not found');
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
                ->where('workspace_id', (string) $channel->workspace_id)
                ->where('user_id', $userId)
                ->exists();

        if (!$isWorkspaceMember) {
            abort(403, 'User is no longer a workspace member');
        }

        $team = Team::where('_id', $channel->team_id)
            ->orWhere('id', $channel->team_id)
            ->first();

        if (!$team) {
            abort(404, 'Team not found');
        }

        if ((string) $team->workspace_id !== (string) $channel->workspace_id) {
            abort(403, 'Channel team does not belong to its workspace');
        }

        $teamMemberIds = collect($team->members ?? [])
            ->map(fn ($memberId) => (string) $memberId)
            ->filter()
            ->values()
            ->all();

        $isTeamMember = in_array($userId, $teamMemberIds, true)
            || \DB::collection('team_members')
                ->where('team_id', (string) $channel->team_id)
                ->where('user_id', $userId)
                ->exists();

        if (!$isTeamMember) {
            abort(403, 'User is no longer a team member');
        }

        $members = collect($channel->members ?? []);
        $alreadyMember = $members->contains(fn ($member) => (string) data_get($member, 'user_id') === $userId);

        if (!$alreadyMember) {
            $members->push(['user_id' => $userId, 'role' => 'member']);
        }

        $remainingJoinRequests = $joinRequests
            ->reject(function ($joinRequest) use ($userId) {
                return (string) data_get($joinRequest, 'user_id') === $userId
                    && (string) data_get($joinRequest, 'status') === 'pending';
            })
            ->values()
            ->all();

        return [
            'members' => $members->values()->all(),
            'join_requests' => $remainingJoinRequests,
        ];
    }
}