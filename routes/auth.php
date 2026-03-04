<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//user sign-up route
Route::post('/signup', [AuthController::class, 'signup'])->middleware([
    'check.validation:signup_request',
    'check.user.exists',
]);

//verify sign-up route
Route::post('/verify-signup', [AuthController::class, 'verifySignup'])->middleware([
    'check.validation:verify_signup_request',
    'check.token:signup_verification_token',
]);

//user login route
Route::post('/login', [AuthController::class, 'login'])->middleware([
    'check.validation:login_request',
    'check.credentials',
    'check.active',
]);

//forgot password route
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware([
    'check.validation:forgot_password_request',
    'check.user.exists.forgot',
]);

//reset password
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware([
    'check.validation:reset_password_request',
    'check.token:forgot_password_token',
]);

//user logout route
Route::post('/logout', [AuthController::class, 'logout'])->middleware([
    'check.token:login_token',
]);
