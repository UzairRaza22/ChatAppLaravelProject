<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;
use App\Http\Resources\ChannelResource;

class ChannelController extends Controller
{
    // Create a channel
    public function create(CreateChannelRequest $request)
    {
        $data = $request->validated();
        $channel = $request->attributes->get('existing_direct_channel') ?? Channel::create($data);
        return response()->success(new ChannelResource($channel), 'Channel created successfully!');
    }

    // Read channel
    public function read(ReadChannelRequest $request, $id)
    {
        return response()->success(new ChannelResource($request->attributes->get('channel')), 'Channel retrieved successfully!');
    }

    // Update channel (admin only)
    public function update(UpdateChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->update($request->validated());
        return response()->success(new ChannelResource($channel), 'Channel updated successfully!');
    }

    // Delete channel (admin only)
    public function delete(DeleteChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->forceDelete();
        return response()->success(null, 'Channel deleted successfully!');
    }

    // Add member 
    public function addMember(AddMemberRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->members = $request->validated()['members'];
        $channel->save();

        return response()->success(new ChannelResource($channel), 'Member added to channel successfully!');
    }

    // Remove member 
    public function removeMember(RemoveMemberRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->members = $request->validated()['members'];
        $channel->save();

        return response()->success(new ChannelResource($channel), 'Member removed from channel successfully!');
    }
}
