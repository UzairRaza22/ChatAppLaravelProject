<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Http\Requests\Message\CreateMessageRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of messages.
     */
    public function readAll(Request $request)
    {
        $messages = Message::with(['sender', 'channel', 'replyTo'])
            ->whereHas('channel', function($query) use ($request) {
                $query->whereHas('members', function($subQuery) use ($request) {
                    $subQuery->where('user_id', $request->user()->_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    /**
     * Store a newly created message.
     */
    public function create(CreateMessageRequest $request)
    {
        $channel = $request->user()->channels()->findOrFail($request->channel_id);
        $replyTo = Message::where('id', $request->reply_to_id)
            ->where('channel_id', $channel->id)
            ->firstOrFail();

        $message = Message::create([
            'content' => $request->content,
            'channel_id' => $channel->id,
            'sender_id' => $request->user()->id,
            'reply_to_id' => $request->reply_to_id,
        ]);

        return new MessageResource($message->load(['sender', 'channel', 'replyTo']));
    }

    /**
     * Update the specified message.
     */
    public function update(UpdateMessageRequest $request, $id)
    {
        $message = Message::where('sender_id', $request->user()->id)
            ->findOrFail($id);

        $message->update([
            'content' => $request->content,
            'edited_at' => now(),
        ]);

        return new MessageResource($message->load(['sender', 'channel', 'replyTo']));
    }

    /**
     * Display the specified message.
     */
    public function read(Request $request, $id)
    {
        $message = Message::with(['sender', 'channel', 'replyTo'])
            ->whereHas('channel', function ($query) use ($request) {
                $query->whereHas('members', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                });
            })
            ->findOrFail($id);

        return new MessageResource($message);
    }

    /**
     * Remove the specified message.
     */
    public function delete(Request $request, $id)
    {
        $message = Message::where('sender_id', $request->user()->id)
            ->findOrFail($id);

        $message->delete();

        return new SuccessResource(['message' => 'Message deleted successfully']);
    }
}
