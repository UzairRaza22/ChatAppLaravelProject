<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FcmTokenController;

Route::middleware('check.token:login_token')->group(function () {
    Route::post('/devices/fcm-token', [FcmTokenController::class, 'store']);
    Route::delete('/devices/fcm-token', [FcmTokenController::class, 'destroy']);
});
