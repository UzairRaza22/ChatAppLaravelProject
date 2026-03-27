<?php

namespace App\Http\Controllers;

use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;
use App\Http\Requests\Channel\ListUserChannelsRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Resources\ChannelResource;
use App\Models\Channel;
class ChannelController extends Controller
{

public function create(CreateChannelRequest $request)
{
    $user = $request->user();

    $channel = Channel::create([
        'workspace_id' => data_get($request, 'workspace_id'),
        'team_id'      => data_get($request, 'team_id'),
        'name'         => data_get($request, 'name'),
        'type'         => data_get($request, 'type', 'public'),
        'created_id'   => (string) data_get($user, '_id'),

        'members' => [
            [
                'user_id' => (string) data_get($user, '_id'),
                'role' => 'creator'
            ]
        ]
    ]);

    return response()->success(new ChannelResource($channel), 'Channel created successfully');
}

    public function read(ReadChannelRequest $request)
    {
        return new ChannelResource(data_get($request->attributes->all(), 'channel'));
    }
    public function listByUser(ListUserChannelsRequest $request)
    {
        $channels = data_get($request->attributes->all(), 'channels', collect());

        return ChannelResource::collection($channels);
    }
    public function update(UpdateChannelRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        $channel->update($request->validated());

        return new ChannelResource($channel);
    }
    public function delete(DeleteChannelRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        $channel->forceDelete();

        return response()->success(null, 'Channel deleted successfully!');
    }
    public function addMember(AddMemberRequest $request)
    {
    $channel = data_get($request, 'channel');
    $memberIds = data_get($request, 'member_ids', []);

    $newMembers = collect($memberIds)->map(function ($id) {
        return [
            'user_id' => (string) $id,
            'role' => 'member'
        ];
    })->toArray();

    $channel->push('members', $newMembers, true);

    return response()->success(new ChannelResource($channel), 'Members added successfully');
    }
    public function removeMember(RemoveMemberRequest $request)
    {
    $channel = data_get($request, 'channel');
    $memberIds = data_get($request, 'member_ids', []);

    $remainingMembers = collect($channel->members)
        ->reject(function ($member) use ($memberIds) {
            return in_array($member['user_id'], $memberIds);
        })
        ->values()
        ->toArray();

    $channel->members = $remainingMembers;
    $channel->save();

    return response()->success(new ChannelResource($channel), 'Members removed successfully');
    }

}