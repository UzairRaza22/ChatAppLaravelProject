<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

// Admin Read Users (all or single)
Route::middleware([
    'check.admin',
    'check.tokens',
    'check.validation:user_read_request'
])->group(function () {
    Route::get('/read', [AdminUserController::class, 'read']);
});
