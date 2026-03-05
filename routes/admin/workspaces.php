<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminWorkspaceController;

// Admin Read Workspaces (all or single)
Route::middleware([
    'check.admin',           // Only admin can access
    'check.tokens',     // Static admin token check
    'check.validation:workspace_read_request'
])->group(function () {
    Route::get('/read', [AdminWorkspaceController::class, 'read']);
});
