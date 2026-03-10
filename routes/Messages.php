<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Message Routes
|--------------------------------------------------------------------------
*/

Route::middleware('check.token:login_token')->group(function () {

    // ── Send Message (DM or Channel) ──────────────────────────────────────
    Route::post('/send', [MessageController::class, 'create'])->middleware([
        'check.validation:SendMessageRequest',

        'message.receiver.check',       // DM: checks receiver + finds shared workspace
        'message.channel.check',        // Channel: checks membership + loads workspace
        'message.file.upload',          // Handles GridFS upload if file present

        'message.workspace.member',
        'message.receiver.check',
        'message.channel.check',
        'message.notification',

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
        'check.validation:UpdateMessageRequest',
        'message.exists',
        'message.sender',
        'message.file.upload',          // Handles GridFS replacement if file present
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
