<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\SearchResource;
use App\Http\Requests\Message\MessageSearchRequest;
use App\Models\Message;
use App\Models\Channel;
use App\Jobs\SendScheduledMessageJob;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\EventService;

class MessageController extends Controller
{
    protected AttachmentService $attachmentService;
    protected EventService $eventService;
    /*
    |--------------------------------------------------------------------------
    | AttachmentService handles ALL GridFS operations:
    |   upload()  → stores file + .meta.json sidecar in GridFS, returns metadata
    |   stream()  → reads + streams file from GridFS with correct disposition
    |   delete()  → removes file + .meta.json sidecar from GridFS
    |--------------------------------------------------------------------------
    */
    public function __construct(AttachmentService $attachmentService, EventService $eventService)
    {
        $this->attachmentService = $attachmentService;
        $this->eventService = $eventService;
    }
    
    private function channelMemberIds($channelOrId): array
    {
        $channel = is_object($channelOrId) ? $channelOrId : Channel::where('_id', $channelOrId)->first();
        if (!$channel) return [];

        return collect($channel->members ?? [])
            ->map(function ($m) {
                if (is_array($m) && isset($m['user_id'])) return (string) $m['user_id'];
                if (is_array($m) && isset($m['id'])) return (string) $m['id'];
                if (is_object($m) && isset($m->user_id)) return (string) $m->user_id;
                if (is_object($m) && isset($m->id)) return (string) $m->id;
                if (is_string($m)) return (string) $m;
                return null;
            })
            ->filter()
            ->values()
            ->unique()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Message  (directchannel | channelmessage)
    | channel → resolved by CheckChannelMessageMiddleware
    | Payload: channel_id, message, file (optional)
    |
    | AttachmentService::upload() stores the file in GridFS and returns
    | file_path, file_name, file_mime, message_type ready for MongoDB.
    |--------------------------------------------------------------------------
    */
    public function create(SendMessageRequest $request)
    {
        $user    = $request->user();

        // Get channel from middleware or look it up if not available
        $channel = $request->attributes->get('channel');
        if (!$channel) {
            $channelId = $request->input('channel_id');
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }
        }

        $fileData = $request->hasFile('file')
            ? $this->attachmentService->upload($request->file('file'), (string) $channel->workspace_id)
            : ['file_path' => null, 'file_name' => null, 'file_mime' => null, 'message_type' => 'text'];

        $scheduleTime = $request->filled('schedule_time')
            ? Carbon::parse($request->input('schedule_time'), 'Asia/Karachi')
            : null;

        $status = $scheduleTime ? 'scheduled' : 'sent';
        $scheduleTimeUtc = $scheduleTime ? $scheduleTime->copy()->utc() : null;

        $message = Message::add([
            'workspace_id' => (string) $channel->workspace_id,
            'sender_id'    => (string) $user->_id,
            'channel_id'   => (string) $channel->_id,
            'message_type' => $fileData['message_type'],
            'content'      => $request->input('message'),
            'file_path'    => $fileData['file_path'],
            'file_name'    => $fileData['file_name'],
            'file_mime'    => $fileData['file_mime'],
            'audio_duration' => $request->input('audio_duration'),
            'schedule_time' => $scheduleTimeUtc,
            'status'        => $status,
        ]);

        if ($scheduleTime) {
            SendScheduledMessageJob::dispatch((string) $message->_id)
                ->delay($scheduleTimeUtc);
        }
    // eent
    $event = [
    'eventName' => 'message_created',
    'module' => 'message',
    'operation' => 'create',
    'referenceId' => (string) $message->_id,
    'userIds' => $this->channelMemberIds($channel),
    'metadata' => [
        'message' => new MessageResource($message->load(['sender', 'channel']))
    ]
];

$this->eventService->send($event);

        return response()->success(
            ['message' => MessageResource::make($message->load(['sender', 'channel']))],
            'Message sent successfully!',
            201
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Read Messages — unified for directchannel and channelmessage
    | Payload: channel_id, cursor (optional), limit (optional, default 20)
    | resolved_messages → set by CheckReadMessagesMiddleware (cursor-based, newest first)
    |--------------------------------------------------------------------------
    */
    public function read(Request $request)
    {
        return response()->success(
            ['messages' => $request->attributes->get('resolved_messages')],
            'Messages retrieved successfully!'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Message  (sender only)
    | Payload: channel_id, message_id, message (content and/or file)
    | message → resolved by CheckMessageExistsMiddleware
    |
    | When a new file is present:
    |   1. AttachmentService::delete() removes old GridFS file + sidecar
    |   2. AttachmentService::upload() stores the new file, returns metadata
    | When no new file: existing file metadata is preserved as-is.
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $message = $request->attributes->get('message');
        $fileData = $request->hasFile('file')
            ? $this->replaceFile($message, $request)
            : ['file_path' => $message->file_path, 'file_name' => $message->file_name, 'file_mime' => $message->file_mime];

        $updated = Message::edit([
            'content'   => $request->input('message'),
            'file_path' => $fileData['file_path'],
            'file_name' => $fileData['file_name'],
            'file_mime' => $fileData['file_mime'],
            'audio_duration' => $request->input('audio_duration'),
        ], $message);
    // eent
    $event = [
    'eventName' => 'message_updated',
    'module' => 'message',
    'operation' => 'update',
    'referenceId' => (string) $updated->_id,
    'userIds' => $this->channelMemberIds($message->channel_id),
    'metadata' => [
        'message' => new MessageResource($updated->load(['sender', 'channel']))
    ]
];

$this->eventService->send($event);

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
    |
    | AttachmentService::delete() removes the GridFS file + sidecar first,
    | then the message record is soft-deleted from MongoDB.
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        $message = $request->attributes->get('message');

        if ((string) $message->sender_id !== (string) $request->user()->_id) {
            return response()->forbidden('Only the sender can delete this message.');
        }
        $messageId = $message->_id ?? $message->id;
    $channelId = $message->channel_id;

        $message->file_path && $this->attachmentService->delete($message->file_path);

        $message->delete();
    // eent
    $event = [
        'eventName' => 'message_deleted',
        'module' => 'message',
        'operation' => 'delete',
        'referenceId' => (string) $messageId,
        'userIds' => $this->channelMemberIds($channelId),
        'metadata' => [
            'messageId' => (string) $messageId,
            'channelId' => (string) $channelId
        ]
    ];

    $this->eventService->send($event);
        return response()->success(null, 'Message deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Download File
    | file_path → validated + set by CheckMessageFileMiddleware
    |
    | AttachmentService::stream() reads file + .meta.json from GridFS and
    | returns a streamed response with correct Content-Type and disposition.
    |--------------------------------------------------------------------------
    */
    public function download(Request $request)
    {
        return $this->attachmentService->stream(
            $request->attributes->get('file_path')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Messages as Read  (read receipts)
    | Payload: channel_id, message_ids[]
    | resolved_read_count → set by CheckReadByMiddleware
    |--------------------------------------------------------------------------
    */
    public function markReadBy(Request $request)
    {
        $count = $request->attributes->get('resolved_read_count', 0);

        return response()->success(
            ['updated' => $count],
            'Messages marked as read successfully!'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Smart Emoji Reaction  (replace, delete on double-click, or add)
    | Payload: channel_id, message_ids[], emoji
    |
    | Behavior:
    |   - First click on emoji → Add reaction
    |   - Switch to different emoji → Replace old with new
    |   - Double-click same emoji → Delete reaction
    |
    | Resolved by CheckMessageReactionMiddleware:
    |   - message: The message document
    |   - resolved_emoji: The emoji being reacted with
    |   - user_current_reaction: User's current reaction (null if none)
    |   - is_double_click: Boolean indicating double-click
    |--------------------------------------------------------------------------
    */
    public function react(Request $request)
    {
        $message  = $request->attributes->get('message');
        $emoji    = $request->attributes->get('resolved_emoji');
        $currentReaction = $request->attributes->get('user_current_reaction'); // null if no reaction
        $isDoubleClick = $request->attributes->get('is_double_click', false); // true if same emoji
        $user     = $request->user();
        $userId   = (string) $user->_id;
        // Smart reaction update (handles replacement, deletion, addition)
        $fresh = Message::toggleReaction(
            $message,
            $userId,
            $emoji,
            $currentReaction,
            $isDoubleClick
        );
        // eent
        $event = [
    'eventName' => 'message_reaction_updated',
    'module' => 'message',
    'operation' => 'reaction',
    'referenceId' => (string) $message->_id,
    'userIds' => $this->channelMemberIds($message->channel_id),
    'metadata' => [
        'message' => new MessageResource($fresh->load(['sender', 'channel'])),
        'emoji' => $emoji,
        'userId' => $userId
    ]
];

$this->eventService->send($event);
        return response()->success(
            ['message' => MessageResource::make($fresh->load(['sender', 'channel']))],
            'Reaction updated successfully!'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search Messages
    |--------------------------------------------------------------------------
    */
    public function search(MessageSearchRequest $request)
    {
        // Get search results from middleware
        $searchResults = $request->get('search_results');
        $searchParams = $request->get('search_params');

        return response()->success(
            \App\Http\Resources\SearchResource::collection($searchResults),
            'Messages retrieved successfully!'
        );
    }

    /*
    | Private: Replace File on Update
    | Deletes the old GridFS file then uploads the new one.
    | Extracted to keep update() clean while grouping related service calls.
    |--------------------------------------------------------------------------
    */
    private function replaceFile($message, Request $request): array
    {
        $message->file_path && $this->attachmentService->delete($message->file_path);

        return $this->attachmentService->upload(
            $request->file('file'),
            (string) $message->workspace_id
        );
    }
}
