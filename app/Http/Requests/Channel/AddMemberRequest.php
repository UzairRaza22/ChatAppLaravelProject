<?php

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,_id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $channel = $this->channel;

            if (!$channel) {
                $validator->errors()->add('channel', 'Channel not found.');
                return;
            }

            $userId = (string) $this->input('user_id');

            $workspaceMember = \DB::collection('workspace_members')
                ->where('workspace_id', $channel->workspace_id)
                ->where('user_id', $userId)
                ->exists();

            $teamMember = \DB::collection('team_members')
                ->where('team_id', $channel->team_id)
                ->where('user_id', $userId)
                ->exists();

            if (!$workspaceMember || !$teamMember) {
                $validator->errors()->add('user_id', 'User must belong to the same workspace and team.');
                return;
            }

            $alreadyMember = collect($channel->members ?? [])->contains(function ($member) use ($userId) {
                return (string) ($member['user_id'] ?? '') === $userId;
            });

            if ($alreadyMember) {
                $validator->errors()->add('user_id', 'User is already a member of this channel.');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $channel = $this->channel;

        $members = $channel->members ?? [];
        $members[] = [
            'user_id' => (string) $data['user_id'],
            'role' => 'member',
        ];

        $data['members'] = collect($members)->values()->all();

        return $data;
    }
}
