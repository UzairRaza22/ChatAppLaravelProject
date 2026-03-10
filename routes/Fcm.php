<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FcmTokenController;

Route::middleware('check.token:login_token')->group(function () {
    Route::post('/devices/fcm-token', [FcmTokenController::class, 'create']);
    Route::delete('/devices/fcm-token', [FcmTokenController::class, 'delete']);
});
