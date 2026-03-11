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
        $user      = $request->user();
        $workspace = data_get($request, 'workspace'); // set by middleware

        $messageData = [
            'workspace_id' => $workspace->_id,
            'sender_id'    => $user->_id,
            'receiver_id'  => $request->input('receiver_id'), 
            'channel_id'   => $request->input('channel_id'),  
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

            // Agar content nahi hai to type 'file' hogi, warna 'text' (jis mein file attachment hai)
            $messageData['message_type'] = $request->input('content') ? 'text' : 'file';
        }

        $message = Message::add($messageData);

        // FCM Notification
        if ($request->input('receiver_id')) {
            $preview = $request->input('content') ? substr($request->input('content'), 0, 100) : 'Sent a file';
            \App\Jobs\SendMessagePushNotificationJob::dispatch(
                (string) $request->input('receiver_id'),
                'New message',
                $preview,
                ['type' => 'message', 'message_id' => (string)$message->_id, 'sender_id' => (string)$user->_id]
            );
        }

        return response()->success([
            'message' => MessageResource::make($message->load(['sender', 'receiver', 'channel']))
        ], 'Message sent successfully!', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Messages (Unified for DM and Channel)
    |--------------------------------------------------------------------------
    */
    public function getMessages(Request $request)
    {
        $user      = $request->user();
        $workspace = data_get($request, 'workspace');
        $receiver  = data_get($request, 'receiver'); // From CheckReceiverInWorkspaceMiddleware
        $channel   = data_get($request, 'channel');  // From CheckChannelInWorkspaceMiddleware

        $query = Message::where('workspace_id', $workspace->_id);

        if ($receiver) {
            // Direct Messages Logic
            $query->where(function ($q) use ($user, $receiver) {
                $q->where(function ($sub) use ($user, $receiver) {
                    $sub->where('sender_id', $user->_id)->where('receiver_id', $receiver->_id);
                })->orWhere(function ($sub) use ($user, $receiver) {
                    $sub->where('sender_id', $receiver->_id)->where('receiver_id', $user->_id);
                });
            })->whereNull('channel_id');
            
            $type = 'direct';
        } elseif ($channel) {
            // Channel Messages Logic
            $query->where('channel_id', $channel->_id)->whereNull('receiver_id');
            $type = 'channel';
        } else {
            return response()->error('Receiver or Channel must be specified.', 400);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->success([
            'type'     => $type,
            'messages' => MessageResource::collection($messages)
        ], ucfirst($type) . ' messages retrieved successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Message
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $message = data_get($request, 'message'); // Set by message.exists middleware
        $updateData = ['content' => $request->input('content')];

        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $directory = "workspaces/{$message->workspace_id}/messages";

            // Purani file delete karna
            if ($message->file_path && Storage::disk('public')->exists($message->file_path)) {
                Storage::disk('public')->delete($message->file_path);
            }

            $path = $file->store($directory, 'public');

            $updateData['file_path'] = $path;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_mime'] = $file->getMimeType();
        }

        $updatedMessage = Message::edit($updateData, $message);

        return response()->success([
            'message' => MessageResource::make($updatedMessage->load(['sender', 'receiver']))
        ], 'Message updated successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Message
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        $message = data_get($request, 'message');
        
        // Agar file hai to storage se delete karein
        if ($message->file_path) {
            Storage::disk('public')->delete($message->file_path);
        }

        $message->delete();

        return response()->success(null, 'Message deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Download File
    |--------------------------------------------------------------------------
    */
    public function download(Request $request)
    {
        $fullPath = data_get($request, 'full_path');
        $fileName = data_get($request, 'file_name');

        if (!Storage::disk('public')->exists($fullPath)) {
            return response()->error('File not found.', 404);
        }

        return response()->download(storage_path("app/public/{$fullPath}"), $fileName);
    }
}