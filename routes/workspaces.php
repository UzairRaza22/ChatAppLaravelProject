<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;

/*
|--------------------------------------------------------------------------
| Workspace Routes
|--------------------------------------------------------------------------
*/

// Protected routes (token authentication required)
Route::middleware(['api.token'])->group(function () {
    // Workspace CRUD operations
    Route::get('/', [WorkspaceController::class, 'readAll']);
    Route::post('/', [WorkspaceController::class, 'create']);
    Route::get('/{id}', [WorkspaceController::class, 'read'])->middleware('workspace.access');
    Route::put('/{id}', [WorkspaceController::class, 'update'])->middleware('workspace.access');
    Route::delete('/{id}', [WorkspaceController::class, 'delete'])->middleware(['workspace.access', 'workspace.ownership']);
    
    // Workspace member management
    Route::post('/{id}/add-member', [WorkspaceController::class, 'addMember'])->middleware(['workspace.access', 'workspace.ownership']);
});
