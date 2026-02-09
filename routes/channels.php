<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChannelController;

/*
|--------------------------------------------------------------------------
| Channel Routes
|--------------------------------------------------------------------------
*/

// Protected routes (token authentication required)
Route::middleware(['api.token'])->group(function () {
    // Channel CRUD operations
    Route::get('/', [ChannelController::class, 'readAll']);
    Route::post('/', [ChannelController::class, 'create']);
    Route::get('/{id}', [ChannelController::class, 'read'])->middleware('channel.access');
    Route::put('/{id}', [ChannelController::class, 'update'])->middleware('channel.access');
    Route::delete('/{id}', [ChannelController::class, 'delete'])->middleware(['channel.access', 'channel.ownership']);
    
    // Channel member management
    Route::post('/{id}/add-member', [ChannelController::class, 'addMember'])->middleware(['channel.access', 'channel.ownership']);
});
