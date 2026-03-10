<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Message Routes
|--------------------------------------------------------------------------
|
| Single /send  → DM (receiver_id) or Channel (channel_id)
| Single /messages → Get DM or Channel messages
|
| Workspace is resolved INSIDE middleware:
|   DM      → CheckReceiverInWorkspaceMiddleware finds shared workspace
|   Channel → CheckChannelInWorkspaceMiddleware loads channel + workspace
|
*/

Route::middleware('check.token:login_token')->group(function () {

    // ── Send Message (DM or Channel) ──────────────────────────────────────
    Route::post('/send', [MessageController::class, 'create'])->middleware([
        'check.validation:send_message_request',
        'check.message.workspace.member',
        'check.message.receiver.check',
        'check.message.channel.check',
    ]);

    // ── Get Direct Messages between auth user and a receiver ──────────────
    Route::get('/direct', [MessageController::class, 'getDirectMessages'])->middleware([
        'check.validation:get_direct_messages_request',
        'check.message.workspace.member',
        'check.message.receiver.check',
    ]);

    // ── Read Messages (DM or Channel) ─────────────────────────────────────
    Route::get('/read', [MessageController::class, 'readMessages'])->middleware([
        'check.validation:GetMessagesRequest',
        'message.receiver.check',       // DM: checks receiver + finds shared workspace
        'message.channel.check',        // Channel: checks membership + loads workspace
        'message.read.resolve',         // Resolves and merges messages into request
    ]);

    // ── Update Message (sender only) ──────────────────────────────────────
    Route::patch('/update', [MessageController::class, 'update'])->middleware([
        'check.validation:update_message_request',
        'check.message.workspace.member',
        'check.message.exists',
        'check.message.sender.check',
    ]);

    // ── Delete Message (sender only) ──────────────────────────────────────
    Route::delete('/delete', [MessageController::class, 'delete'])->middleware([
        'check.validation:delete_message_request',
        'check.message.workspace.member',
        'check.message.exists',
        'check.message.sender.check',
    ]);

    // ── Download File ─────────────────────────────────────────────────────
    Route::get('/download', [MessageController::class, 'download'])->middleware([
        'message.file.check',
    ]);
});
