<?php

namespace App\Http\Requests\Channel;

use App\Models\Channel;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class JoinPublicChannelRequest extends FormRequest
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
        $user = $this->user() ?? $this->input('verified_user');
        $authUserId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());
        $userId = (string) $data['user_id'];

        if ($userId !== $authUserId) {
            abort(403, 'You can request join only for your own user_id');
        }

        if ((string) $channel->type !== 'public') {
            abort(403, 'Only public channels accept join requests');
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
            abort(403, 'User must be part of the workspace');
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
            abort(403, 'User must be part of the team');
        }

        $alreadyMember = collect($channel->members ?? [])
            ->contains(fn ($member) => (string) data_get($member, 'user_id') === $userId);
        if ($alreadyMember) {
            abort(400, 'User is already a member of this channel');
        }

        $joinRequests = collect($channel->join_requests ?? []);

        $alreadyPending = $joinRequests->contains(function ($joinRequest) use ($userId) {
            return (string) data_get($joinRequest, 'user_id') === $userId
                && (string) data_get($joinRequest, 'status') === 'pending';
        });

        if ($alreadyPending) {
            abort(400, 'Join request is already pending');
        }

        $joinRequests->push([
            'user_id' => $userId,
            'status' => 'pending',
            'requested_at' => now()->toISOString(),
        ]);

        return [
            'join_requests' => $joinRequests->values()->all(),
        ];
    }
}