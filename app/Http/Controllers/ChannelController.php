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
    // 1. Create Channel
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
                    'role' => 'admin' // Creator gets admin role
                ]
            ]
        ]);

        return response()->success(new ChannelResource($channel), 'Channel created successfully');
    }

    // 2. Read Channel
    public function read(ReadChannelRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        if ($channel) {
            return response()->success(new ChannelResource($channel), 'Channel retrieved successfully');
        }

        // Get all channels and filter by user (like teams do)
        $channels = data_get($request->attributes->all(), 'channels', collect());
        $user = $request->user();
        $userId = (string) data_get($user, '_id');
        
        // Filter channels where user is creator OR member
        $userChannels = $channels->filter(function ($channel) use ($userId) {
            // Check if user is creator
            if ((string) $channel->created_id === $userId) {
                return true;
            }
            
            // Check if user is member
            $members = $channel->members ?? [];
            if (is_array($members)) {
                foreach ($members as $member) {
                    if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId) {
                        return true;
                    }
                }
            }
            
            return false;
        });
        
        return response()->success(ChannelResource::collection($userChannels), 'Channels retrieved successfully');
    }

    // 3. List Channels by User
    public function listByUser(ListUserChannelsRequest $request)
    {
        // Get all channels and filter by user (same as read method)
        $channels = data_get($request->attributes->all(), 'channels', collect());
        $user = $request->user();
        $userId = (string) data_get($user, '_id');
        
        // Filter channels where user is creator OR member
        $userChannels = $channels->filter(function ($channel) use ($userId) {
            // Check if user is creator
            if ((string) $channel->created_id === $userId) {
                return true;
            }
            
            // Check if user is member
            $members = $channel->members ?? [];
            if (is_array($members)) {
                foreach ($members as $member) {
                    if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId) {
                        return true;
                    }
                }
            }
            
            return false;
        });
        
        return response()->success(ChannelResource::collection($userChannels), 'Channels retrieved successfully');
    }

    // 4. Update Channel
    public function update(UpdateChannelRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        $channel->update($request->validated());

        return response()->success(new ChannelResource($channel), 'Channel updated successfully');
    }

    // 5. Delete Channel
    public function delete(DeleteChannelRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        $channel->forceDelete();

        return response()->success(null, 'Channel deleted successfully');
    }

    // 6. Add Members
    public function addMember(AddMemberRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        $userIds = data_get($request, 'user_ids', []);
        
        // Get current members (with correct structure)
        $currentMembers = $channel->members ?? [];
        
        // Add new members with the correct structure: {"user_id": "...", "role": "member"}
        foreach ($userIds as $userId) {
            $currentMembers[] = [
                'user_id' => (string) $userId,
                'role' => 'member' // Default role for new members
            ];
        }
        
        // Update the members array with the correct structure
        $channel->update(['members' => $currentMembers]);

        return response()->success(new ChannelResource($channel->fresh()), 'Members added successfully');
    }

    // 7. Remove Members
    public function removeMember(RemoveMemberRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        $userIds = data_get($request, 'user_ids', []);
        
        // Get current members and remove the specified user IDs
        $currentMembers = collect($channel->members ?? []);
        $updatedMembers = $currentMembers->reject(function ($member) use ($userIds) {
            if (is_array($member) && isset($member['user_id'])) {
                return in_array((string) $member['user_id'], array_map('strval', $userIds));
            }
            if (is_object($member) && isset($member->user_id)) {
                return in_array((string) $member->user_id, array_map('strval', $userIds));
            }
            // Fallback for simple string members
            if (is_string($member)) {
                return in_array((string) $member, array_map('strval', $userIds));
            }
            return false;
        })->values()->all();
        
        $channel->update(['members' => $updatedMembers]);

        return response()->success(new ChannelResource($channel->fresh()), 'Members removed successfully');
    }
}
