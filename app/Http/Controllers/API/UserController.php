<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;


class UserController extends Controller
{
    //


    public function me(Request $request)
    {

        try {
            $currentToken = $request->bearerToken();

            $user = $currentToken ? PersonalAccessToken::findToken($currentToken)->tokenable : null;
            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => [

                    'phone' => $user->phone_number,
                    'email' => $user->email ?? NULL,
                    'access_token' => $currentToken,
                    'token_type' => 'Bearer',


                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 410);
        }
    }

    public function onboard(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|string',
                'medical_info' => 'required|json',
                'emergency_contacts' => 'required|json',
                'license_number' => 'required|string|max:255',
                'files.*' => 'file|max:2048',
            ]);
            $accessTokenExpiresAt = Carbon::now()->addDays(7);
            $refreshTokenExpiresAt = Carbon::now()->addDays(14);
            $user = User::create([
                'name' => $request->name,
                'medical_info' => $request->medical_info,
                'emergency_contacts' => $request->emergency_contacts,
                'license_number' => $request->license_number,
                'password' => bcrypt('qwertyuiop'),
            ]);
            $user->assignRole($request->role);
            // Create access and refresh tokens
            $accessToken = $user->createToken('access_token', ['*'], $accessTokenExpiresAt)->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh'], $refreshTokenExpiresAt)->plainTextToken;


            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $user->addMedia($file)
                        ->toMediaCollection('documents', 'normaluser');
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [

                    'user' => $user,
                    'access_token' => $accessToken,
                    'access_token_expires_at' => $accessTokenExpiresAt,
                    'refresh_token' => $refreshToken,
                    'refresh_token_expires_at' => $refreshTokenExpiresAt,
                    'token_type' => 'Bearer',


                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
