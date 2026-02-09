<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;

/*
|--------------------------------------------------------------------------
| Team Routes
|--------------------------------------------------------------------------
*/

// Protected routes (token authentication required)
Route::middleware(['api.token'])->group(function () {
    // Team CRUD operations
    Route::get('/', [TeamController::class, 'readAll']);
    Route::post('/', [TeamController::class, 'create']);
    Route::get('/{id}', [TeamController::class, 'read'])->middleware('team.access');
    Route::put('/{id}', [TeamController::class, 'update'])->middleware('team.access');
    Route::delete('/{id}', [TeamController::class, 'delete'])->middleware(['team.access', 'team.ownership']);
    
    // Team member management
    Route::post('/{id}/add-member', [TeamController::class, 'addMember'])->middleware(['team.access', 'team.ownership']);
});
