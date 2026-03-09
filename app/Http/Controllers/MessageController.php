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
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $user        = $request->user();
        $workspace   = data_get($request, 'workspace');
        $messageData = [
            'workspace_id' => $workspace->_id,
            'sender_id'    => $user->_id,
            'receiver_id'  => $request->input('receiver_id'),
            'channel_id'   => $request->input('channel_id'),
            'message_type' => $request->input('message_type', 'text'),
            'content'      => $request->input('content'),
        ];

        // Handle file upload
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

        // FCM INTEGRATION: dispatch push notification job
        if ($request->input('receiver_id')) {
            $preview = $request->input('content') ? substr($request->input('content'), 0, 100) : 'Sent a file';
            \App\Jobs\SendMessagePushNotificationJob::dispatch(
                (string) $request->input('receiver_id'),
                'New message',
                $preview,
                ['type' => 'message', 'message_id' => (string)$message->id, 'sender_id' => (string)$user->_id]
            );
        }

        return response()->success([
                'message' => MessageResource::make($message->load(['sender', 'receiver', 'channel']))
            ], 'Message sent successfully!', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Direct Messages between two users
    |--------------------------------------------------------------------------
    */
    public function getDirectMessages(Request $request)
    {
        $user      = $request->user();
        $receiver  = data_get($request, 'receiver');
        $workspace = data_get($request, 'workspace');

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

        return response()->success([
                'messages' => MessageResource::collection($messages)
            ], 'Direct messages retrieved successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Get Channel Messages
    |--------------------------------------------------------------------------
    */
    public function getChannelMessages(Request $request)
    {
        $channel = data_get($request, 'channel');

        $messages = Message::where('channel_id', $channel->_id)
            ->whereNull('receiver_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->success([
                'messages' => MessageResource::collection($messages)
            ], 'Channel messages retrieved successfully!');
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
            $workspace = data_get($request, 'workspace');
            $file      = $request->file('file');
            $directory = "workspaces/{$workspace->_id}/messages";

            // Delete old file if present
            if ($message->file_path && Storage::disk('public')->exists($message->file_path)) {
                Storage::disk('public')->delete($message->file_path);
            }

            $path = $file->store($directory, 'public');

            $updateData['file_path'] = $path;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_mime'] = $file->getMimeType();
        }

        $message = Message::edit($updateData, $message);

        return response()->success([
                'message' => MessageResource::make($message->load(['sender', 'receiver']))
            ], 'Message updated successfully!');
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

        return response()->success(null, 'Message deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Download File
    | — all validation is handled by CheckMessageFileMiddleware
    |--------------------------------------------------------------------------
    */
    public function download(Request $request)
    {
        $fullPath = data_get($request, 'full_path');
        $fileName = data_get($request, 'file_name');

        return response()->download($fullPath, $fileName);
    }
}
