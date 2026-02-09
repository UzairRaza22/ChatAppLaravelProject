<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OTP Authentication
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login-with-otp', [AuthController::class, 'loginWithOtp']);
Route::post('/delete-account', [AuthController::class, 'deleteOtpVerification']);
Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Email verification
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

// Public users listing (separate from auth prefix)
Route::get('/users', [AuthController::class, 'users']);

// Protected routes (authentication required)
Route::middleware(['api.token'])->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Email verification
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);
    Route::get('/email/verify', [AuthController::class, 'checkEmailVerification']);

    // Token management routes
    Route::prefix('tokens')->group(function () {
        Route::get('/', [AuthController::class, 'tokens']);
        Route::post('/', [AuthController::class, 'createToken']);
        Route::get('/current', [AuthController::class, 'currentToken']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
        Route::put('/{id}/revoke', [AuthController::class, 'revokeToken']);
        Route::delete('/{id}', [AuthController::class, 'deleteToken']);
    });
});
