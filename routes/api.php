<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegistrationController;
use App\Http\Controllers\API\SignatureController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;


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
    
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'We could not find a user with that email address.',
        ], 404);
    }

    
    $status = Password::sendResetLink($request->only('email'));

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email.',
        ]);
    }

    
    return response()->json([
        'success' => false,
        'message' => 'Unable to send reset link. Please try again later.',
    ], 400);
});


Route::post('/staff-login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    $user = User::where('email', $request->email)->first();
    $isStaff = Staff::where('email', $request->email)->where('is_approved',1)->first();
    if (!$user || !$isStaff || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }


    $token = $user->createToken('staff-token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => $user
    ]);
});
