<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::middleware('check.token:login_token', 'check.active')->group(function () {

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
        'check.message.file.check',
    ]);
});
