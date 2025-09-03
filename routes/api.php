<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegistrationController;
use App\Http\Controllers\API\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('signup', RegistrationController::class)->only(['index', 'store', 'update', 'destroy'])
->middleware('auth:sanctum');
Route::post('/refresh', [RegistrationController::class, 'refreshToken'])
->middleware('auth:sanctum');
Route::post('/verifyOtp', [RegistrationController::class, 'verifyOtp'])
->middleware('auth:sanctum');
Route::get('/me', [UserController::class, 'me'])
->middleware('auth:sanctum');
Route::post('/onboard', [UserController::class, 'onboard'])
->middleware('auth:sanctum');
