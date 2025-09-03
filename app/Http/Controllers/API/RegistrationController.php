<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Notifications\OtpNotification;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {


            $validator = Validator::make($request->all(), [

                'phone_number' => 'required|numeric|unique:users,phone_number',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $user = User::create([
                'phone_number' => $request->phone_number,
                'password' => bcrypt('qwertyuiop'),
            ]);


            $accessTokenExpiresAt = Carbon::now()->addDays(7);
            $refreshTokenExpiresAt = Carbon::now()->addDays(14);

            // Create access and refresh tokens
            $accessToken = $user->createToken('access_token', ['*'], $accessTokenExpiresAt)->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh'], $refreshTokenExpiresAt)->plainTextToken;





            // send OTP notification via email
            $otpCode = rand(100000, 999999);
            $user->otp_code = $otpCode;
            $user->otp_expires_at = now()->addMinutes(5);
            $user->save();

            $user->notify(new OtpNotification($otpCode));

            //  send OTP notification via sms
            $this->sendOtpSms($request->phone_number, $otpCode);


            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [

                    'phone' => $user->phone_number,
                    'email' => $user->email ?? NULL,
                    'access_token' => $accessToken,
                    'access_token_expires_at' => $accessTokenExpiresAt,
                    'refresh_token' => $refreshToken,
                    'refresh_token_expires_at' => $refreshTokenExpiresAt,
                    'token_type' => 'Bearer',


                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in store method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }





    public function refreshToken(Request $request)
    {
        $currentRefreshToken = $request->bearerToken();
        $refreshToken = PersonalAccessToken::findToken($currentRefreshToken);

        if (!$refreshToken || !$refreshToken->can('refresh') || $refreshToken->expires_at->isPast()) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $refreshToken->tokenable;
        $refreshToken->delete();

        $accessTokenExpiresAt = Carbon::now()->addDays(7);
        $refreshTokenExpiresAt = Carbon::now()->addDays(14);

        $newAccessToken = $user->createToken('access_token', ['*'], $accessTokenExpiresAt)->plainTextToken;
        $newRefreshToken = $user->createToken('refresh_token', ['refresh'], $refreshTokenExpiresAt)->plainTextToken;

        return response()->json([
            'access_token' => $newAccessToken,
            'access_token_expires_at' => $accessTokenExpiresAt,
            'refresh_token' => $newRefreshToken,
            'refresh_token_expires_at' => $refreshTokenExpiresAt,
            'token_type' => 'Bearer',
        ]);
    }


    public function sendOtpSms($phoneNumber, $otp)
    {
        $message = 'Your OTP verification code is ' . $otp . ' your OTP is valid for 5 minutes.';

        $base_uri = config('services.swiftsms.baseUri');
        $endpoint = config('services.swiftsms.endpoint');
        $senderId = config('services.swiftsms.senderId');
        $token = config('services.swiftsms.token');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->get($base_uri . $endpoint, [
            'sender_id' => $senderId,
            'numbers' => $phoneNumber,
            'message' => $message,
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Swift SMS send failed', ['response' => $response->body()]);
        return false;
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp_code' => 'required|numeric|exists:users,otp_code',
                'phone_number' => 'required|numeric|exists:users,phone_number',
            ]);

            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user || $user->otp_code !== $request->otp_code || now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['error' => 'Invalid or expired OTP code'], 422);
            }

            $user->is_onboarded = true;
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in verifyOtp method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
