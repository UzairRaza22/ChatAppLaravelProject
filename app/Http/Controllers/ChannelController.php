<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use App\Http\Resources\ChannelResource;
use App\Models\Channel;
use App\Http\Requests\Channel\CreateChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * Display a listing of channels.
     */
    public function readAll(Request $request)
    {
        $channels = Channel::with(['creator', 'members', 'workspace', 'team'])
            ->whereHas('members', function($query) use ($request) {
                $query->where('user_id', $request->user()->_id);
            })
            ->paginate(20);

        return ChannelResource::collection($channels);
    }

    /**
     * Store a newly created channel.
     */
    public function create(CreateChannelRequest $request)
    {
        $workspace = $request->user()->workspaces()->findOrFail($request->workspace_id);
        $team = $request->user()->teams()->findOrFail($request->team_id);

        $channel = Channel::create([
            'name' => $request->name,
            'description' => $request->description,
            'workspace_id' => $workspace->id,
            'team_id' => $request->team_id,
            'type' => $request->type,
            'created_by' => $request->user()->id,
        ]);

        $channel->members()->attach($request->user()->id, ['role' => 'admin']);

        return new ChannelResource($channel->load(['creator', 'members', 'workspace', 'team']));
    }

    /**
     * Display the specified channel.
     */
    public function read(Request $request, $id)
    {
        $channel = $request->user()->channels()
            ->with(['creator', 'members', 'workspace', 'team'])
            ->findOrFail($id);

        return new ChannelResource($channel);
    }

    /**
     * Update the specified channel.
     */
    public function update(UpdateChannelRequest $request, $id)
    {
        $channel = $request->user()->channels()->findOrFail($id);
        $channel->update($request->validated());

        return new ChannelResource($channel->load(['creator', 'members', 'workspace', 'team']));
    }

    /**
     * Remove the specified channel.
     */
    public function delete(Request $request, $id)
    {
        $channel = $request->user()->channels()->findOrFail($id);
        $channel->delete();

        return new SuccessResource(['message' => 'Channel deleted successfully']);
    }
}
