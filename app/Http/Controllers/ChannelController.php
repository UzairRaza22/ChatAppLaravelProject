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
    public function create(CreateChannelRequest $request)
    {
        $channel = Channel::create($request->validated());
        return new ChannelResource($channel);
    }

    public function read(ReadChannelRequest $request, $id)
    {
        return new ChannelResource($request->channel);
    }

    public function update(UpdateChannelRequest $request, $id)
    {
        $channel = $request->channel;
        $channel->update($request->validated());
        return new ChannelResource($channel);
    }

    public function delete(DeleteChannelRequest $request, $id)
    {
        $channel = $request->channel;
        $channel->delete();

        return response()->json(['message' => 'Channel deleted successfully']);
    }

    public function addMember(AddMemberRequest $request, $id)
    {
        $channel = $request->channel;
        $channel->members = $request->validated()['members'];
        $channel->save();

        return new ChannelResource($channel);
    }

    public function removeMember(RemoveMemberRequest $request, $id)
    {
        $channel = $request->channel;
        $channel->members = $request->validated()['members'];
        $channel->save();

        return new ChannelResource($channel);
    }
}
