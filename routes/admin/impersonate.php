<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminImpersonateController;

// Admin Impersonate User (start impersonation)
Route::middleware([
    'check.admin',
    'check.tokens',
    'check.validation:impersonate_read_request'
])->group(function () {
    Route::get('/read', [AdminImpersonateController::class, 'read']);
    Route::post('/stop', [AdminImpersonateController::class, 'stop']);
});
