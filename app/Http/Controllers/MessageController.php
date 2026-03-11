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
    | Create Message  (directchannel | channelmessage)
    | channel   → resolved + validated by CheckChannelMessageMiddleware
    | Both use the same endpoint — unified route POST /send
    | Payload: channel_id, message (content and/or file)
    | file_path, file_name, file_mime, message_type → set by CheckMessageFileUploadMiddleware
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $user    = $request->user();
        $channel = $request->attributes->get('channel');

        $message = Message::add([
            'workspace_id' => (string) $channel->workspace_id,
            'sender_id'    => (string) $user->_id,
            'channel_id'   => (string) $channel->_id,
            'message_type' => $request->attributes->get('resolved_message_type', 'text'),
            'content'      => $request->input('message'),
            'file_path'    => $request->attributes->get('file_path'),
            'file_name'    => $request->attributes->get('file_name'),
            'file_mime'    => $request->attributes->get('file_mime'),
        ]);

        return response()->success(
            ['message' => MessageResource::make($message->load(['sender', 'channel']))],
            'Message sent successfully!',
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Read Direct Channel Messages  (directchannel)
    | Payload: channel_id
    | resolved_messages → set by CheckReadMessagesMiddleware (paginated, newest first)
    |--------------------------------------------------------------------------
    */
    public function readMessages(Request $request)
    {
        return response()->success(
            ['messages' => $request->attributes->get('resolved_messages')],
            'Direct channel messages retrieved successfully!'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Read Channel Messages  (channelmessage)
    | Payload: channel_id
    | resolved_messages → set by CheckReadMessagesMiddleware (paginated, newest first)
    |--------------------------------------------------------------------------
    */
    public function readChannelMessages(Request $request)
    {
        return response()->success(
            ['messages' => $request->attributes->get('resolved_messages')],
            'Channel messages retrieved successfully!'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Message  (sender only)
    | Payload: channel_id, message_id, message (content and/or file)
    | message   → resolved by CheckMessageExistsMiddleware
    | file data → resolved by CheckMessageFileUploadMiddleware
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $message = $request->attributes->get('message');

        $updated = Message::edit([
            'content'   => $request->input('message'),
            'file_path' => $request->attributes->get('file_path'),
            'file_name' => $request->attributes->get('file_name'),
            'file_mime' => $request->attributes->get('file_mime'),
        ], $message);

        return response()->success(
            ['message' => MessageResource::make($updated->load(['sender', 'channel']))],
            'Message updated successfully!'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Message  (soft delete, sender only)
    | Payload: channel_id, message_id
    | message → resolved by CheckMessageExistsMiddleware
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        $request->attributes->get('message')->delete();

        return response()->success(null, 'Message deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Download File
    | file_path, file_name → set by CheckMessageFileMiddleware
    | Streams file directly from GridFS
    |--------------------------------------------------------------------------
    */
    public function download(Request $request)
    {
        $filePath = $request->attributes->get('file_path');
        $fileName = $request->attributes->get('file_name');

        $fileContents = Storage::disk('gridfs')->get($filePath);

        return response($fileContents, 200)
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Type', Storage::disk('gridfs')->mimeType($filePath) ?? 'application/octet-stream');
    }
}