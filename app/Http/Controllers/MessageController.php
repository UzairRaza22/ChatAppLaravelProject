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

        return response()->success([
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
