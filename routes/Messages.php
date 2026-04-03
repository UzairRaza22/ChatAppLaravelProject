<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

// Register Route::read as a GET macro
Route::macro('read', function ($uri, $action) {
    return Route::get($uri, $action);
});

// Register Route::update as a PATCH macro
Route::macro('update', function ($uri, $action) {
    return Route::patch($uri, $action);
});

Route::middleware(['check.token:login_token', 'check.active'])->group(function () {

    // ── POST /messages/create ─────────────────────────────────────────────
    // Unified send for both directchannel and channelmessage
    // Payload: channel_id, message, file (optional)
    Route::post('/create', [MessageController::class, 'create'])->middleware([
        // 'message.channel.check',    // Temporarily removed for debugging
        'message.file.upload',      // handles GridFS upload if file present
    ]);

    // ── GET /messages/read ────────────────────────────────────────────────
    // Unified read for both directchannel and channelmessage
    // Payload: channel_id
    // Returns: paginated 20 messages, newest first
    Route::read('/read', [MessageController::class, 'read'])->middleware([
        'message.read.resolve',     // validates channel membership + paginates messages
    ]);

    // ── PATCH /messages/update ────────────────────────────────────────────
    // Update a message (sender only)
    // Payload: channel_id, message_id, message, file (optional)
    Route::match(['PATCH', 'POST'], '/update', [MessageController::class, 'update'])->middleware([
        'message.channel.check',
        'message.exists',           // resolves message by message_id + channel_id
        'message.sender',           // checks auth user is the sender
        'message.file.upload',      // handles GridFS upload if file present
    ]);

    // ── DELETE /messages/delete ───────────────────────────────────────────
    // Soft delete a message (sender only)
    // Payload: channel_id, message_id
    Route::delete('/delete', [MessageController::class, 'delete'])->middleware([
        'message.exists',           // resolves message by message_id + channel_id
        'message.sender',           // checks auth user is the sender
    ]);

    // ── GET /messages/download ────────────────────────────────────────────
    // Download file from GridFS
    // Query param: ?path=workspaces/{workspace_id}/messages/{filename}
    Route::get('/download', [MessageController::class, 'download'])->middleware([
        'message.file.check',       // validates file exists in GridFS
    ]);

    // ── POST /messages/read-by ────────────────────────────────────────────
    // Bulk mark messages as read by the authenticated user
    // Payload: channel_id, message_ids[]
    Route::post('/read-by', [MessageController::class, 'markReadBy'])->middleware([
        'message.channel.check',    // resolves channel + validates membership
        'message.readby',           // validates message_ids, calls Message::markReadBy
    ]);

    // ── POST /messages/react ──────────────────────────────────────────────
    // Toggle an emoji reaction on a message (add if not present, remove if present)
    // Payload: channel_id, message_ids[], emoji
    Route::post('/react', [MessageController::class, 'react'])->middleware([
        'message.channel.check',    // resolves channel + validates membership
        'message.react',            // validates emoji, resolves message from message_ids[0]
    ]);

    // ── GET /messages/search ───────────────────────────────────────────────
    // Search messages in database with filters
    // Query params: query (required), channel_id (optional), page (optional), per_page (optional)
    Route::get('/search', [MessageController::class, 'search'])->middleware([
        'check.token:login_token',    // requires authentication
        'check.active',            // requires active user
        'message.search',            // validates search params, performs search
    ]);
});
