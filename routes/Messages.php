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
*/

Route::middleware(['check.token:login_token', 'check.active'])->group(function () {

    // ── Send Message (DM or Channel) ──────────────────────────────────────
    Route::post('/send', [MessageController::class, 'create'])->middleware([
        'check.validation:SendMessageRequest',
        'check.message.workspace.member',
        'check.message.receiver.check',   // DM check
        'check.message.channel.check',    // Channel check
        'check.message.file.check',
    ]);

    // ── Get Messages (DM or Channel) ──────────────────────────────────────
    Route::get('/messages', [MessageController::class, 'getMessages'])->middleware([
        'check.validation:GetMessagesRequest',
        'check.message.workspace.member',
        'check.message.receiver.check',
        'check.message.channel.check',
    ]);

    // ── Update Message (sender only) ──────────────────────────────────────
    Route::patch('/update', [MessageController::class, 'update'])->middleware([
        'check.validation:UpdateMessageRequest',
        'check.message.workspace.member',
        'check.message.exists',
        'check.message.sender.check',
    ]);

    // ── Delete Message (sender only) ──────────────────────────────────────
    Route::delete('/delete', [MessageController::class, 'delete'])->middleware([
        'check.validation:DeleteMessageRequest',
        'check.message.workspace.member',
        'check.message.exists',
        'check.message.sender.check',
    ]);

    // ── Download File ─────────────────────────────────────────────────────
    Route::get('/download', [MessageController::class, 'download'])->middleware([
        'check.message.file.check',
    ]);
});