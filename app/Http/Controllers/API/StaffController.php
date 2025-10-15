<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Models\EmergencyHelp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class StaffController extends Controller
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

        Log::info('Data from Mobile App - Personal Details: ' . json_encode($request->all()));

        try {
            $validatedData = $request->validate([
                'phone' => 'required|string|max:50|unique:staff,phone',
                'fullName' => 'required|string|max:255',
                'email' => 'nullable|string|max:255',
                'address' => 'required|string|max:1000',
                'password' => 'required|string',
                'hpczNumber' => 'required|string|max:10',
                'nrcNumber' => 'required',
                'nrc' => 'required|string',
                'selfie' => 'required|string',
            ]);




            //Createas User for Login first
            $user = User::Create([
                'name' => $validatedData['fullName'],
                'phone_number' => $validatedData['phone'],
                'email' => $validatedData['email'] ?? NULL,
                'password' => bcrypt($validatedData['password']),
            ]);

            $staff = Staff::create([
                'phone' => $validatedData['phone'],
                'full_name' => $validatedData['fullName'],
                'email' => $validatedData['email'] ?? null,
                'address' => $validatedData['address'],
                'password' => bcrypt($validatedData['password']),
                'hpcz_number' => $validatedData['hpczNumber'] ?? null,
                'is_approved' => 2
            ]);

            // Process each base64 file
            $uploadedFiles = [];

            if ($request->has('nrc')) {
                $uploadedFiles['nrc'] = $this->processBase64File($request->nrc, 'nrc', $user->id, ['jpeg', 'png', 'pdf']);
            }

            if ($request->has('selfie')) {
                $uploadedFiles['selfie'] = $this->processBase64File($request->selfie, 'selfie', $user->id, ['jpeg', 'png']);
            }

            foreach ($uploadedFiles as $collection => $fileInfo) {
                $staff->addMedia($fileInfo['path'])
                    ->toMediaCollection($collection, 'staffs');
            }






            // $user->assignRole('medical_staff');
            $accessTokenExpiresAt = Carbon::now()->addDays(7);
            $refreshTokenExpiresAt = Carbon::now()->addDays(14);

            // Create access and refresh tokens
            $accessToken = $user->createToken('access_token', ['*'], $accessTokenExpiresAt)->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh'], $refreshTokenExpiresAt)->plainTextToken;

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
                'message' => 'Cant process your request right now ensure you are not entring duplicate records',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    private function processBase64File($base64Data, $prefix, $userId, $allowedExtensions = ['jpeg', 'png', 'pdf'])
    {
        // Extract mime type and base64 data
        if (!preg_match('/^data:([a-zA-Z]+\/[a-zA-Z\+]+);base64,/', $base64Data, $matches)) {
            throw new \Exception("Invalid base64 format for {$prefix}");
        }

        $mimeType = $matches[1];
        $extension = $this->mimeToExtension($mimeType);

        // Validate extension
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("Invalid file type for {$prefix}. Allowed: " . implode(', ', $allowedExtensions));
        }

        // Extract base64 data
        $data = substr($base64Data, strpos($base64Data, ',') + 1);
        $fileData = base64_decode($data, true);

        if ($fileData === false) {
            throw new \Exception("Invalid base64 data for {$prefix}");
        }

        // Validate file size (5MB = 5 * 1024 * 1024 bytes)
        $fileSize = strlen($fileData);
        if ($fileSize > 5120 * 1024) {
            throw new \Exception("File size too large for {$prefix}. Maximum 5MB allowed");
        }

        // Generate unique filename
        $filename = "{$prefix}_{$userId}_" . Str::random(10) . '.' . $extension;
        $path = "{$userId}/{$filename}";

        // Save file to storage
        Storage::disk('staffs')->put($path, $fileData);

        return [
            'path' => Storage::disk('staffs')->path($path),
            'filename' => $filename,
            'size' => $fileSize,
            'mime_type' => $mimeType
        ];
    }

    /**
     * Convert mime type to file extension
     */
    private function mimeToExtension($mimeType)
    {
        $mimeMap = [
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'image/webp' => 'webp',
        ];

        return $mimeMap[$mimeType] ?? 'bin';
    }



    public function updateLocation(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'fcm_token' => 'sometimes|string',
        ]);

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $staff->update([
            'last_known_latitude' => $request->latitude,
            'last_known_longitude' => $request->longitude,
            'fcm_token' => $request->fcm_token ?? $staff->fcm_token,
        ]);

        return response()->json(['message' => 'Location updated successfully']);
    }



    /**
     * Return all active staff with location coordinates
     */
    public function listActiveStaffs()
    {


        $staffs = Staff::where('is_approved', 1)
            ->whereNotNull('last_known_latitude')
            ->whereNotNull('last_known_longitude')
            ->select('id', 'full_name', 'phone', 'last_known_latitude', 'last_known_longitude')
            ->get();

        return response()->json([
            'success' => true,
            'staffs' => $staffs,
        ]);
    }


    /**
     * Return and list Emergency Statuses for each Staff
     */
    public function listEmergencyStatuses($staffId)
    {
        try {
            // Validate that the staff exists and is approved
            $userStaff = User::find($staffId);

            $staff = Staff::where('email', $userStaff->email)
                ->where('is_approved', 1)
                ->first();

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found or not approved',
                ], 404);
            }

            // Get emergency help requests attended by this staff member
            $emergencyStatuses = EmergencyHelp::where('attended_by', $staffId)
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'phone',
                    'latitude',
                    'longitude',
                    'notes',
                    'description',
                    'attended_by',
                    'closest_staff_distance',
                    'active',
                    'completed',
                    'created_at',
                    'updated_at'
                ])
                ->map(function ($emergency) {

                    if ($emergency->completed) {
                        $emergency->status = 1;
                    } elseif ($emergency->active) {
                        $emergency->status = 1;
                    } else {
                        $emergency->status = 1;
                    }

                    // Add location object expected by React Native
                    $emergency->location = [
                        'latitude' => $emergency->latitude,
                        'longitude' => $emergency->longitude
                    ];

                    return $emergency;
                });

            return response()->json([
                'success' => true,
                'emergencies' => $emergencyStatuses,
                'count' => $emergencyStatuses->count(),
                'staff' => [
                    'id' => $staff->id,
                    'full_name' => $staff->full_name,
                    'phone' => $staff->phone,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching emergency statuses: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch emergency statuses',
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
}
