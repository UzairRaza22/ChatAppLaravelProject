<?php

use App\Http\Controllers\ChannelController;
use App\Http\Middleware\Channel\ChannelAdminMiddleware;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Middleware\Channel\MemberCheckMiddleware;
use Illuminate\Support\Facades\Route;
Route::middleware(['check.token:login_token', 'check.active'])->group(function () {
    Route::post('/create', [ChannelController::class, 'create'])
        ->middleware('check.validation:CreateChannelRequest')
        ->middleware([MemberCheckMiddleware::class, 'channel.create']);

    Route::get('/read', [ChannelController::class, 'read'])
        ->middleware('check.validation:ReadChannelRequest')
        ->middleware(ChannelExistMiddleware::class);

 Route::get('/list-by-user', [ChannelController::class, 'listByUser'])

        ->middleware('check.validation:ListUserChannelsRequest')

        ->middleware(ChannelExistMiddleware::class);

    Route::patch('/update', [ChannelController::class, 'update'])
        ->middleware('check.validation:UpdateChannelRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    Route::delete('/delete', [ChannelController::class, 'delete'])
        ->middleware('check.validation:DeleteChannelRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class]);

    Route::post('/add-member', [ChannelController::class, 'addMember'])
        ->middleware('check.validation:AddMemberRequest')
        ->middleware([ChannelExistMiddleware::class, MemberCheckMiddleware::class, 'channel.add.member']);

    Route::delete('/remove-member', [ChannelController::class, 'removeMember'])
        ->middleware('check.validation:RemoveMemberRequest')
        ->middleware([ChannelExistMiddleware::class, ChannelAdminMiddleware::class, 'channel.remove.member']);

});
