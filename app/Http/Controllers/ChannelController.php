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
            'members'      => [
                [
                    'user_id' => (string) data_get($user, '_id'),
                    'role'    => 'admin'
                ]
            ]
        ]);

        return response()->success(new ChannelResource($channel), 'Channel created successfully');
    }

    public function read(ReadChannelRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        if ($channel) {
            return response()->success(new ChannelResource($channel), 'Channel retrieved successfully');
        }

        $channels = data_get($request->attributes->all(), 'channels', collect());
        return response()->success(ChannelResource::collection($channels), 'Channels retrieved successfully');
    }

    public function listByUser(ListUserChannelsRequest $request)
    {
        $channels = data_get($request->attributes->all(), 'channels', collect());
        $user     = $request->user();
        $userId   = (string) data_get($user, '_id');

        $userChannels = $channels->filter(function ($channel) use ($userId) {
            if ((string) $channel->created_id === $userId) {
                return true;
            }

            foreach ($channel->members as $member) {
                $memberId = null;

                if (is_array($member) && isset($member['user_id'])) {
                    $memberId = (string) $member['user_id'];
                } elseif (is_array($member) && isset($member['id'])) {
                    $memberId = (string) $member['id'];
                } elseif (is_object($member) && isset($member->user_id)) {
                    $memberId = (string) $member->user_id;
                } elseif (is_object($member) && isset($member->id)) {
                    $memberId = (string) $member->id;
                } elseif (is_string($member)) {
                    $memberId = $member;
                }

                if ($memberId === $userId) {
                    return true;
                }
            }

            return false;
        });

        return response()->success(ChannelResource::collection($userChannels), 'Channels retrieved successfully');
    }

    public function update(UpdateChannelRequest $request)
    {
        // Channel resolved by ChannelExistMiddleware — no workspace_id needed here
        $channel = data_get($request->attributes->all(), 'channel');
        $channel->update($request->validated());

        return response()->success(new ChannelResource($channel), 'Channel updated successfully');
    }

    public function delete(DeleteChannelRequest $request)
    {
        // Channel resolved by ChannelExistMiddleware — no workspace_id needed here
        $channel = data_get($request->attributes->all(), 'channel');
        $channel->forceDelete();

        return response()->success(null, 'Channel deleted successfully');
    }

    public function addMember(AddMemberRequest $request)
    {
        $channel  = data_get($request->attributes->all(), 'channel');
        $userIds  = data_get($request, 'user_ids', []);

        $currentMembers = $channel->members;

        // Avoid duplicates
        $existingIds = array_map(fn($m) => (string) ($m['user_id'] ?? ''), $currentMembers);

        foreach ($userIds as $userId) {
            if (!in_array((string) $userId, $existingIds, true)) {
                $currentMembers[] = [
                    'user_id' => (string) $userId,
                    'role'    => 'member'
                ];
            }
        }

        $channel->update(['members' => $currentMembers]);

        return response()->success(new ChannelResource($channel->fresh()), 'Members added successfully');
    }

    public function removeMember(RemoveMemberRequest $request)
    {
        $channel  = data_get($request->attributes->all(), 'channel');
        $userIds  = array_map('strval', data_get($request, 'user_ids', []));

        $updatedMembers = collect($channel->members)->reject(function ($member) use ($userIds) {
            if (is_array($member) && isset($member['user_id'])) {
                return in_array((string) $member['user_id'], $userIds, true);
            }
            if (is_object($member) && isset($member->user_id)) {
                return in_array((string) $member->user_id, $userIds, true);
            }
            if (is_string($member)) {
                return in_array($member, $userIds, true);
            }
            return false;
        })->values()->all();

        $channel->update(['members' => $updatedMembers]);

        return response()->success(new ChannelResource($channel->fresh()), 'Members removed successfully');
    }
}