<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminTeamController;

// Admin Read Teams (all or single)
Route::middleware([
    'check.admin',
    'check.tokens',
    'check.validation:team_read_request'
])->group(function () {
    Route::get('/read', [AdminTeamController::class, 'read']);
});
