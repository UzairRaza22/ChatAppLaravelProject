<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| File Routes
|--------------------------------------------------------------------------
*/

// Protected routes (token authentication required)
Route::middleware(['api.token'])->group(function () {
    // File CRUD operations
    Route::get('/', [FileController::class, 'readAll']);
    Route::post('/', [FileController::class, 'create']);
    Route::get('/{id}', [FileController::class, 'read']);
    Route::put('/{id}', [FileController::class, 'update']);
    Route::get('/{id}/download', [FileController::class, 'download'])->name('files.download');
    Route::delete('/{id}', [FileController::class, 'delete']);
});
