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
use Illuminate\Support\Facades\Http;
use App\Services\EventService;
class ChannelController extends Controller
{
    public function create(CreateChannelRequest $request)
    {
        $user = $request->user();
        $type = data_get($request, 'type', 'public');

        $members = $type === 'direct'
            ? (data_get($request, 'members') ?: [
                [
                    'user_id' => (string) data_get($user, '_id'),
                    'role'    => 'creator',
                ],
            ])
            : [
                [
                    'user_id' => (string) data_get($user, '_id'),
                    'role'    => 'admin',
                ],
            ];

        $channelData = [
            'workspace_id' => data_get($request, 'workspace_id'),
            'team_id'      => data_get($request, 'team_id'),
            'name'         => data_get($request, 'name'),
            'type'         => $type,
            'created_id'   => (string) data_get($user, '_id'),
            'members'      => $members,
        ];

        if ($type === 'direct' && data_get($request, 'direct_id')) {
            $channelData['direct_id'] = data_get($request, 'direct_id');
        }

        $channel = Channel::create($channelData);
    //eent
     $event = [
            'eventName' => 'channel_created',
            'module' => 'channel',
            'operation' => 'create',
            'referenceId' => $channel->_id ?? $channel->id,
            'userIds' => $this->channelMemberIds($channel),
            'metadata' => [
                'channel' => new ChannelResource($channel)
            ]
        ];

        $this->sendEvent($event);

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
    //eent
      $event = [
            'eventName' => 'channel_updated',
            'module' => 'channel',
            'operation' => 'update',
            'referenceId' => $channel->_id ?? $channel->id,
            'userIds' => $this->channelMemberIds($channel),
            'metadata' => [
                'channel' => new ChannelResource($channel)
            ]
        ];

        $this->sendEvent($event);

        return response()->success(new ChannelResource($channel), 'Channel updated successfully');
    }

    public function delete(DeleteChannelRequest $request)
    {
        // Channel resolved by ChannelExistMiddleware — no workspace_id needed here
        $channel = data_get($request->attributes->all(), 'channel');
        $channelId = $channel->_id ?? $channel->id;

    $memberIds = collect($channel->members ?? [])
        ->map(fn ($m) => $m['user_id'] ?? $m->user_id ?? $m)
        ->map(fn ($id) => (string) $id)
        ->toArray();

        $channel->forceDelete();
    // eent
      $event = [
        'eventName' => 'channel_deleted',
        'module' => 'channel',
        'operation' => 'delete',
        'referenceId' => (string) $channelId,
        'userIds' => $memberIds,
        'metadata' => [
            'channelId' => (string) $channelId
        ]
    ];

    $this->sendEvent($event);


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
    //eent
      $event = [
            'eventName' => 'channel_member_added',
            'module' => 'channel',
            'operation' => 'member_added',
            'referenceId' => $channel->_id ?? $channel->id,
            'userIds' => $this->channelMemberIds($channel),
            'metadata' => [
                'channel' => new ChannelResource($channel),
                'addedUserIds' => $userIds
            ]
        ];

        $this->sendEvent($event);
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
    //eeent
     $event = [
            'eventName' => 'channel_member_removed',
            'module' => 'channel',
            'operation' => 'member_removed',
            'referenceId' => $channel->_id ?? $channel->id,
            'userIds' => array_values(array_unique(array_merge(
                $this->channelMemberIds($channel),
                $userIds
            ))),
            'metadata' => [
                'channel' => new ChannelResource($channel),
                'removedUserIds' => $userIds
            ]
        ];

        $this->sendEvent($event);

        return response()->success(new ChannelResource($channel->fresh()), 'Members removed successfully');
    }
}
