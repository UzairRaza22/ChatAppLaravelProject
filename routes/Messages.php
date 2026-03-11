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

    // ── POST /messages/send ───────────────────────────────────────────────
    // Unified send for both directchannel and channelmessage
    // Payload: channel_id, message, file (optional)
    Route::post('/send', [MessageController::class, 'create'])->middleware([
        'message.channel.check',    // validates channel + membership (direct & public/private)
        'message.file.upload',      // handles GridFS upload if file present
    ]);

    // ── GET /messages/read ────────────────────────────────────────────────
    // Unified read for both directchannel and channelmessage
    // Payload: channel_id
    // Returns: paginated 20 messages, newest first
    Route::read('/read', [MessageController::class, 'readMessages'])->middleware([
        'message.read.resolve',     // validates channel membership + paginates messages
    ]);

    // ── PATCH /messages/update ────────────────────────────────────────────
    // Update a message (sender only)
    // Payload: channel_id, message_id, message, file (optional)
    Route::update('/update', [MessageController::class, 'update'])->middleware([
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
});