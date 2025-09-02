<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Notifications\OtpNotification;
use App\Models\User;

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


            $request->validate([

                'phone' => 'required|numeric|unique:users,phone_number',

            ]);


            $user = User::create([
                'phone_number' => $request->phone_number,
                'password' => bcrypt('qwertyuiop'),
            ]);


            $token = $user->createToken('react-client-token')->plainTextToken;




            // send OTP notification via email
            $otpCode = rand(100000, 999999);
            $user->otp_code = $otpCode;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            $user->notify(new OtpNotification($otpCode));

            //  send OTP notification via sms
            $this->sendOtpSms($request->phone, $otpCode);


            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'token' => $token,

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


    public function sendOtpSms($phoneNumber, $otp)
    {
        $message = 'Your OTP verification code is ' . $otp . ' your OTP is valid for 10 minutes.';

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
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || $user->otp_code !== $request->otp_code || now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['error' => 'Invalid or expired OTP code'], 422);
            }

            $user->is_verified = true;
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
