<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegistrationController;
use App\Http\Controllers\API\SignatureController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Password;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('signup', RegistrationController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('createMedicalStaff', StaffController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('signature', SignatureController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('/refresh', [RegistrationController::class, 'refreshToken'])
->middleware('auth:sanctum');
Route::post('/verifyOtp', [RegistrationController::class, 'verifyOtp'])
->middleware('auth:sanctum');
Route::get('/me', [UserController::class, 'me'])
->middleware('auth:sanctum');
Route::post('/onboard', [UserController::class, 'onboard']);

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Password reset link sent to your email.'])
        : response()->json(['message' => 'Unable to send reset link.'], 400);
});

