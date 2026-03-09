<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Channel;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\User;

class AddMemberRequest extends FormRequest
{
    protected $channel;

    public function authorize()
    {
        // Channel is injected by ChannelExistMiddleware
        $this->channel = $this->attributes->get('channel');

        // Fallback for direct invocation without middleware
        if (!$this->channel && $this->route('id')) {
            $this->channel = Channel::where('_id', $this->route('id'))->first();
        }

        return $this->channel !== null;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,_id',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $userId = $data['user_id'];
        $channel = $this->channel;
        $isDirect = (string) $channel->type === 'direct';

        if ($isDirect) {
            abort(422, 'Cannot add members to a direct channel');
        }

        $workspace = Workspace::find($channel->workspace_id);
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

        if (!in_array((string) $userId, $workspaceMemberIds, true)) {
            abort(403, 'User must be part of the workspace to be added');
        }

        $team = Team::find($channel->team_id);
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

        if (!in_array((string) $userId, $teamMemberIds, true)) {
            abort(403, 'User must be part of the team to be added');
        }

        // Prevent duplicate
        $alreadyMember = collect($channel->members)->contains(
            fn ($member) => (string) ($member['user_id'] ?? '') === (string) $userId
        );
        if ($alreadyMember) {
            abort(400, 'User is already a member of the channel');
        }

        // Append the new member
        $members = $channel->members ?? [];
        $members[] = ['user_id' => $userId, 'role' => 'member'];
        $data['members'] = collect($members)->values()->all();

        return $data;
    }
}
