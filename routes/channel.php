<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChannelController;
use App\Http\Middleware\ChannelExistMiddleware;
use App\Http\Middleware\ChannelAdminMiddleware;
use App\Http\Middleware\MemberCheckMiddleware;

// All routes protected by auth
Route::middleware(['check.token'])->group(function () {

    // Create a new channel
    Route::post('/channels/create', [ChannelController::class, 'create'])
        ->middleware(MemberCheckMiddleware::class);

    // Read channel
    Route::get('/channels/{id}', [ChannelController::class, 'read'])
        ->middleware(ChannelExistMiddleware::class);

    // Update channel (admin only)
    Route::put('/channels/{id}', [ChannelController::class, 'update'])
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    // Delete channel (admin only)
    Route::delete('/channels/{id}', [ChannelController::class, 'delete'])
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    // Add member (admin only)
    Route::post('/channels/{id}/add-member', [ChannelController::class, 'addMember'])
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class, MemberCheckMiddleware::class]);

    // Remove member (admin only)
    Route::post('/channels/{id}/remove-member', [ChannelController::class, 'removeMember'])
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);
});