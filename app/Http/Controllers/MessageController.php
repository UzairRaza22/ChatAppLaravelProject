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
    | workspace resolved by middleware — file stored in GridFS
    | file data merged into request by CheckMessageFileUploadMiddleware
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $user      = $request->user();
        $workspace = data_get($request, 'workspace');

        $message = Message::add([
            'workspace_id' => $workspace->_id,
            'sender_id'    => $user->_id,
            'receiver_id'  => $request->input('receiver_id'),
            'channel_id'   => $request->input('channel_id'),
            'message_type' => data_get($request, 'resolved_message_type', $request->input('message_type', 'text')),
            'content'      => $request->input('content'),
            'file_path'    => data_get($request, 'file_path'),
            'file_name'    => data_get($request, 'file_name'),
            'file_mime'    => data_get($request, 'file_mime'),
        ]);

        return response()->success([
            'message' => MessageResource::make($message->load(['sender', 'receiver', 'channel']))
        ], 'Message sent successfully!', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Read Messages — DM or Channel
    | type resolved by middleware — receiver or channel merged into request
    | Latest message on TOP (desc order)
    |--------------------------------------------------------------------------
    */
    public function readMessages(Request $request)
    {
        $user      = $request->user();
        $workspace = data_get($request, 'workspace');
        $receiver  = data_get($request, 'receiver');
        $channel   = data_get($request, 'channel');

        $messages = data_get($request, 'resolved_messages');

<<<<<<< HEAD
            return response()->json([
                'success' => true,
                'message' => 'Direct messages retrieved successfully!',
                'data'    => [
                    'type'     => 'direct',
                    'messages' => MessageResource::collection($messages)
                ]
            ]);
        }
=======
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
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4

        // ── Channel Messages ──────────────────────────────────────────────
        $messages = Message::where('channel_id', $channel->_id)
            ->whereNull('receiver_id')
            ->orderBy('created_at', 'asc')
            ->get();

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Channel messages retrieved successfully!',
            'data'    => [
                'type'     => 'channel',
=======
        return response()->success([
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
                'messages' => MessageResource::collection($messages)
            ], 'Channel messages retrieved successfully!');
            'type'     => $receiver ? 'direct' : 'channel',
            'messages' => MessageResource::collection($messages),
        ], $receiver ? 'Direct messages retrieved successfully!' : 'Channel messages retrieved successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Message  (only sender)
    | file replacement handled by CheckMessageFileUploadMiddleware
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $message = data_get($request, 'message');

        $message = Message::edit([
            'content'   => $request->input('content'),
            'file_path' => data_get($request, 'file_path'),
            'file_name' => data_get($request, 'file_name'),
            'file_mime' => data_get($request, 'file_mime'),
        ], $message);
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
    | Download File from GridFS
    | path validation handled by CheckMessageFileMiddleware
    | Download File
    | — all validation handled by CheckMessageFileMiddleware
    |--------------------------------------------------------------------------
    */
    public function download(Request $request)
    {
        $filePath    = data_get($request, 'file_path');
        $fileName    = data_get($request, 'file_name');
        $fileContent = Storage::disk('gridfs')->get($filePath);
        $mimeType    = Storage::disk('gridfs')->mimeType($filePath) ?? 'application/octet-stream';

        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
