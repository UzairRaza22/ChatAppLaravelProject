<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

<<<<<<< HEAD
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
=======
Route::middleware('check.token:login_token', 'check.active')->group(function () {
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4

    // ── Send Message (DM or Channel) ──────────────────────────────────────
    Route::post('/send', [MessageController::class, 'create'])->middleware([
<<<<<<< HEAD
        'check.validation:SendMessageRequest',
        'message.receiver.check',   // DM:      checks receiver + finds shared workspace
        'message.channel.check',    // Channel: checks channel membership + loads workspace
    ]);

    // ── Get Messages (DM or Channel) ──────────────────────────────────────
    Route::get('/messages', [MessageController::class, 'getMessages'])->middleware([
        'check.validation:GetMessagesRequest',
        'message.receiver.check',   // DM:      checks receiver + finds shared workspace
        'message.channel.check',    // Channel: checks channel membership + loads workspace
    ]);

    // ── Update Message (sender only) ──────────────────────────────────────
    Route::patch('/update', [MessageController::class, 'update'])->middleware([
        'check.validation:UpdateMessageRequest',
        'message.exists',
        'message.sender',
=======
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

    // ── Get Channel Messages ──────────────────────────────────────────────
    Route::get('/channel', [MessageController::class, 'getChannelMessages'])->middleware([
        'check.validation:get_channel_messages_request',
        'check.message.workspace.member',
        'check.message.channel.check',
    ]);

    // ── Update a message (sender only) ────────────────────────────────────
    Route::patch('/update', [MessageController::class, 'update'])->middleware([
        'check.validation:update_message_request',
        'check.message.workspace.member',
        'check.message.exists',
        'check.message.sender.check',
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
    ]);

    // ── Delete Message (sender only) ──────────────────────────────────────
    Route::delete('/delete', [MessageController::class, 'delete'])->middleware([
<<<<<<< HEAD
        'check.validation:DeleteMessageRequest',
        'message.exists',
        'message.sender',
=======
        'check.validation:delete_message_request',
        'check.message.workspace.member',
        'check.message.exists',
        'check.message.sender.check',
>>>>>>> 171cca664853ef100f35468bb369b1848fd4e0c4
    ]);

    // ── Download File ─────────────────────────────────────────────────────
    Route::get('/download', [MessageController::class, 'download'])->middleware([
        'check.message.file.check',
    ]);
});
