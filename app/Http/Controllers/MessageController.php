<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Create Message  (text | file | text + file)
    | workspace is resolved by middleware (receiver or channel check)
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $user      = $request->user();
        $workspace = data_get($request, 'workspace'); // set by receiver/channel middleware

        $messageData = [
            'workspace_id' => $workspace->_id,
            'sender_id'    => $user->_id,
            'receiver_id'  => $request->input('receiver_id'), // null for channel
            'channel_id'   => $request->input('channel_id'),  // null for DM
            'message_type' => $request->input('message_type', 'text'),
            'content'      => $request->input('content'),
        ];

        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $directory = "workspaces/{$workspace->_id}/messages";
            $path      = $file->store($directory, 'public');

            $messageData['file_path'] = $path;
            $messageData['file_name'] = $file->getClientOriginalName();
            $messageData['file_mime'] = $file->getMimeType();

            $messageData['message_type'] = $request->input('content') ? 'text' : 'file';
        }

        $message = Message::add($messageData);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully!',
            'data'    => [
                'message' => MessageResource::make($message->load(['sender', 'receiver', 'channel']))
            ]
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Messages — single route for DM and Channel
    | DM      → receiver is set by CheckReceiverInWorkspaceMiddleware
    | Channel → channel  is set by CheckChannelInWorkspaceMiddleware
    |--------------------------------------------------------------------------
    */
    public function getMessages(Request $request)
    {
        $user      = $request->user();
        $workspace = data_get($request, 'workspace');
        $receiver  = data_get($request, 'receiver');
        $channel   = data_get($request, 'channel');

        if ($receiver) {
            // ── Direct Messages ───────────────────────────────────────────
            $messages = Message::where('workspace_id', $workspace->_id)
                ->where(function ($query) use ($user, $receiver) {
                    $query->where(function ($q) use ($user, $receiver) {
                        $q->where('sender_id', $user->_id)
                            ->where('receiver_id', $receiver->_id);
                    })->orWhere(function ($q) use ($user, $receiver) {
                        $q->where('sender_id', $receiver->_id)
                            ->where('receiver_id', $user->_id);
                    });
                })
                ->whereNull('channel_id')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Direct messages retrieved successfully!',
                'data'    => [
                    'type'     => 'direct',
                    'messages' => MessageResource::collection($messages)
                ]
            ]);
        }

        // ── Channel Messages ──────────────────────────────────────────────
        $messages = Message::where('channel_id', $channel->_id)
            ->whereNull('receiver_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Channel messages retrieved successfully!',
            'data'    => [
                'type'     => 'channel',
                'messages' => MessageResource::collection($messages)
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Message  (only sender)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $message    = data_get($request, 'message');
        $updateData = ['content' => $request->input('content')];

        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $directory = "workspaces/{$message->workspace_id}/messages";

            if ($message->file_path && Storage::disk('public')->exists($message->file_path)) {
                Storage::disk('public')->delete($message->file_path);
            }

            $path = $file->store($directory, 'public');

            $updateData['file_path'] = $path;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_mime'] = $file->getMimeType();
        }

        $message = Message::edit($updateData, $message);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully!',
            'data'    => [
                'message' => MessageResource::make($message->load(['sender', 'receiver']))
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Message  (soft delete, only sender)
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        $message = data_get($request, 'message');
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully!'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Download File
    | — all validation handled by CheckMessageFileMiddleware
    |--------------------------------------------------------------------------
    */
    public function download(Request $request)
    {
        $fullPath = data_get($request, 'full_path');
        $fileName = data_get($request, 'file_name');

        return response()->download($fullPath, $fileName);
    }
}
