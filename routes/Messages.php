<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::middleware('check.token:login_token')->group(function () {

    // ── Send a message (text | file | text + file) ────────────────────────
    Route::post('/send', [MessageController::class, 'create'])->middleware([
        'check.validation:SendMessageRequest',
        'message.workspace.member',
        'message.receiver.check',
        'message.channel.check',
        'message.notification',
    ]);

    // ── Get Direct Messages between auth user and a receiver ──────────────
    Route::get('/direct', [MessageController::class, 'getDirectMessages'])->middleware([
        'check.validation:GetDirectMessagesRequest',
        'message.workspace.member',
        'message.receiver.check',
    ]);

    // ── Get Channel Messages ──────────────────────────────────────────────
    Route::get('/channel', [MessageController::class, 'getChannelMessages'])->middleware([
        'check.validation:GetChannelMessagesRequest',
        'message.workspace.member',
        'message.channel.check',
    ]);

    // ── Update a message (sender only) ────────────────────────────────────
    Route::patch('/update', [MessageController::class, 'update'])->middleware([
        'check.validation:UpdateMessageRequest',
        'message.workspace.member',
        'message.exists',
        'message.sender',
    ]);

    // ── Delete a message — soft delete (sender only) ──────────────────────
    Route::delete('/delete', [MessageController::class, 'delete'])->middleware([
        'check.validation:DeleteMessageRequest',
        'message.workspace.member',
        'message.exists',
        'message.sender',
    ]);

    // ── Download a file from a message ────────────────────────────────────
    Route::get('/download', [MessageController::class, 'download'])->middleware([
        'message.file.check',
    ]);
});
