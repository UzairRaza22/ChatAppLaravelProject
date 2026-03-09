<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/profile',function() {
    return 'Profile View';
});

Route::get('/test-fcm', function () {
    return view('test-fcm');
});
