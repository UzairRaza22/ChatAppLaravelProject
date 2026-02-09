<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| Message Routes
|--------------------------------------------------------------------------
*/

// Protected routes (token authentication required)
Route::middleware(['api.token'])->group(function () {
    // Message CRUD operations
    Route::get('/', [MessageController::class, 'readAll']);
    Route::post('/', [MessageController::class, 'create']);
    Route::get('/{id}', [MessageController::class, 'read'])->middleware('channel.access');
    Route::put('/{id}', [MessageController::class, 'update'])->middleware('channel.access');
    Route::delete('/{id}', [MessageController::class, 'delete'])->middleware('channel.access');
   
});