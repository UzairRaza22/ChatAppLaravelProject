<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Http\Resources\BaseResource;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Models\Message;
use App\Models\Channel;
use App\Models\Workspace;
use App\Models\Team;
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
                $query->where('type', 'public')
                      ->orWhereJsonContains('user_ids', $request->user()->_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    /**
     * Store a newly created message.
     */
    public function create(\Illuminate\Http\Request $request)
    {
        try {
            // Check if user is authenticated
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty token'
                ], 401);
            }

            // Basic validation with custom error messages
            $request->validate([
                'workspace_id' => 'required|string',
                'team_id' => 'required|string', 
                'channel_id' => 'required|string',
                'content' => 'required|string|max:4000',
            ], [
                'workspace_id.required' => 'Workspace ID is required',
                'workspace_id.string' => 'Workspace ID must be a string',
                'team_id.required' => 'Team ID is required',
                'team_id.string' => 'Team ID must be a string',
                'channel_id.required' => 'Channel ID is required',
                'channel_id.string' => 'Channel ID must be a string',
                'content.required' => 'Content is required',
                'content.string' => 'Content must be a string',
                'content.max' => 'Content must not exceed 4000 characters',
            ]);

            // Validate workspace exists
            $workspace = Workspace::find($request->workspace_id);
            if (!$workspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect workspace ID'
                ], 404);
            }

            // Validate team belongs to workspace
            $team = Team::where('id', $request->team_id)
                ->where('workspace_id', $workspace->id)
                ->first();
            if (!$team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect team ID or team does not belong to this workspace'
                ], 404);
            }

            // Validate channel belongs to team
            $channel = Channel::where('id', $request->channel_id)
                ->where('workspace_id', $workspace->id)
                ->where('team_id', $team->id)
                ->first();
            if (!$channel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect channel ID or channel does not belong to this team'
                ], 404);
            }
            
            // Check if user has access to this channel
            if ($channel->type !== 'public' && !in_array($request->user()->_id, $channel->user_ids ?? [])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this channel'
                ], 403);
            }

            $message = Message::create([
                'content' => $request->content,
                'channel_id' => $channel->id,
                'sender_id' => $request->user()->id,
                'reply_to_id' => $request->reply_to_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message created successfully',
                'data' => [
                    'id' => (string) $message->_id,
                    'content' => $message->content,
                    'channel_id' => (string) $message->channel_id,
                    'sender_id' => (string) $message->sender_id,
                    'reply_to_id' => $message->reply_to_id ? (string) $message->reply_to_id : null,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating message: ' . $e->getMessage()
            ], 500);
        }
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
                $query->where('type', 'public')
                      ->orWhereJsonContains('user_ids', $request->user()->_id);
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
