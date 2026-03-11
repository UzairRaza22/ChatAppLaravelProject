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
        $data = $request->validated();
        $channel = data_get($request->attributes->all(), 'channel') ?? Channel::create($data);

        return new ChannelResource($channel);
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
        $channel = data_get($request->attributes->all(), 'channel');
        data_set($channel, 'members', data_get($request->validated(), 'members'));
        $channel->save();

        return new ChannelResource($channel);
    }

    public function removeMember(RemoveMemberRequest $request)
    {
        $channel = data_get($request->attributes->all(), 'channel');
        data_set($channel, 'members', data_get($request->validated(), 'members'));
        $channel->save();

        return new ChannelResource($channel);
    }
}
