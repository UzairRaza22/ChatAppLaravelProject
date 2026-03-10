<?php

use App\Http\Controllers\ChannelController;
use App\Http\Middleware\Channel\ChannelAdminMiddleware;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Middleware\Channel\MemberCheckMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['check.token:login_token', 'check.active'])->group(function () {
    Route::post('/channels/create', [ChannelController::class, 'create'])
        ->middleware('check.validation:CreateChannelRequest')
        ->middleware(MemberCheckMiddleware::class);

    Route::get('/channels/{id}', [ChannelController::class, 'read'])
        ->middleware('check.validation:ReadChannelRequest')
        ->middleware(ChannelExistMiddleware::class);

    Route::put('/channels/{id}', [ChannelController::class, 'update'])
        ->middleware('check.validation:UpdateChannelRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    Route::delete('/channels/{id}', [ChannelController::class, 'delete'])
        ->middleware('check.validation:DeleteChannelRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    Route::post('/channels/{id}/add-member', [ChannelController::class, 'addMember'])
        ->middleware('check.validation:AddMemberRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class, MemberCheckMiddleware::class]);

    Route::post('/channels/{id}/remove-member', [ChannelController::class, 'removeMember'])
        ->middleware('check.validation:RemoveMemberRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    Route::post('/channels/{id}/join', [ChannelController::class, 'requestJoinPublic'])
        ->middleware('check.validation:JoinPublicChannelRequest')
        ->middleware([ChannelExistMiddleware::class, MemberCheckMiddleware::class]);

    Route::post('/channels/{id}/join-requests/approve', [ChannelController::class, 'approveJoinRequest'])
        ->middleware('check.validation:ApproveJoinRequestRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    Route::post('/channels/{id}/join-requests/reject', [ChannelController::class, 'rejectJoinRequest'])
        ->middleware('check.validation:RejectJoinRequestRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);
});