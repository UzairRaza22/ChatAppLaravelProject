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
    ]);

    // ── Delete Message (sender only) ──────────────────────────────────────
    Route::delete('/delete', [MessageController::class, 'delete'])->middleware([
        'check.validation:DeleteMessageRequest',
        'message.exists',
        'message.sender',
    ]);

    // ── Download File ─────────────────────────────────────────────────────
    Route::get('/download', [MessageController::class, 'download'])->middleware([
        'message.file.check',
    ]);
});
