<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtTokenVerify;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(AuthController::class)->group(
    function () {
        // Public routes for guests
        Route::post('register', 'register');
        Route::post('login', 'login');

        Route::post('password/reset/send/otp',  'sendOtp');
        Route::post('password/reset/verify/otp',  'verifyOtp');
        Route::post('password/reset',  'resetPassword');
    }
);
Route::get('profile', [ProfileController::class, 'profile'])->middleware(JwtTokenVerify::class);
Route::post('logout', [AuthController::class, 'logout'])->middleware(JwtTokenVerify::class);
