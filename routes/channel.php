<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChannelController;
use App\Http\Middleware\ChannelExistMiddleware;
use App\Http\Middleware\ChannelAdminMiddleware;
use App\Http\Middleware\MemberCheckMiddleware;

// All routes protected by auth
Route::middleware(['check.token:login_token', 'check.active'])->group(function () {

Route::post('/channels/create', [ChannelController::class, 'create'])
    ->middleware('check.validation:create_channel_request')
    ->middleware('check.channel.member');

Route::get('/channels/{id}', [ChannelController::class, 'read'])
    ->middleware('check.validation:read_channel_request')
    ->middleware('check.channel.exists');

Route::put('/channels/{id}', [ChannelController::class, 'update'])
    ->middleware('check.validation:update_channel_request')
    ->middleware(['check.channel.exists', 'check.channel.admin']);

Route::delete('/channels/{id}', [ChannelController::class, 'delete'])
    ->middleware('check.validation:delete_channel_request')
    ->middleware(['check.channel.exists', 'check.channel.admin']);

Route::post('/channels/{id}/add-member', [ChannelController::class, 'addMember'])
    ->middleware('check.validation:add_channel_member_request')
    ->middleware(['check.channel.exists', 'check.channel.admin', 'check.channel.member']);

Route::post('/channels/{id}/remove-member', [ChannelController::class, 'removeMember'])
    ->middleware('check.validation:remove_channel_member_request')
    ->middleware(['check.channel.exists', 'check.channel.admin']);
});