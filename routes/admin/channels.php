<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminChannelController;

// Admin Read Channels (all or single)
Route::middleware([
    'check.admin',
    'check.tokens',
    'check.validation:channel_read_request'
])->group(function () {
    Route::get('/read', [AdminChannelController::class, 'read']);
});
