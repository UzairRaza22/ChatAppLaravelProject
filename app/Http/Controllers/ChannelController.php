<?php

namespace App\Http\Controllers;

use App\Http\Requests\Channel\AddMemberRequest;
use App\Http\Requests\Channel\ApproveJoinRequestRequest;
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\DeleteChannelRequest;
use App\Http\Requests\Channel\JoinPublicChannelRequest;
use App\Http\Requests\Channel\ReadChannelRequest;
use App\Http\Requests\Channel\RejectJoinRequestRequest;
use App\Http\Requests\Channel\RemoveMemberRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Resources\ChannelResource;
use App\Models\Channel;

class ChannelController extends Controller
{
    public function create(CreateChannelRequest $request)
    {
        $data = $request->validated();
        $channel = $request->attributes->get('existing_direct_channel') ?? Channel::create($data);

        return new ChannelResource($channel);
    }

    public function read(ReadChannelRequest $request, $id)
    {
        return new ChannelResource($request->attributes->get('channel'));
    }

    public function update(UpdateChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->update($request->validated());

        return new ChannelResource($channel);
    }

    public function delete(DeleteChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->forceDelete();

        return response()->json(['message' => 'Channel deleted successfully']);
    }

    public function addMember(AddMemberRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->members = $request->validated()['members'];
        $channel->save();

        return new ChannelResource($channel);
    }

    public function removeMember(RemoveMemberRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->members = $request->validated()['members'];
        $channel->save();

        return new ChannelResource($channel);
    }

    public function requestJoinPublic(JoinPublicChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->join_requests = $request->validated()['join_requests'];
        $channel->save();

        return new ChannelResource($channel);
    }

    public function approveJoinRequest(ApproveJoinRequestRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $validated = $request->validated();
        $channel->members = $validated['members'];
        $channel->join_requests = $validated['join_requests'];
        $channel->save();

        return new ChannelResource($channel);
    }

    public function rejectJoinRequest(RejectJoinRequestRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->join_requests = $request->validated()['join_requests'];
        $channel->save();

        return new ChannelResource($channel);
    }
}