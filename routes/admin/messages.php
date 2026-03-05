<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminMessageController;

// Admin Read Messages (all or single)
Route::middleware([
    'check.admin',
    'check.tokens',
    'check.validation:message_read_request'
])->group(function () {
    Route::get('/read', [AdminMessageController::class, 'read']);
});
