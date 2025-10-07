<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SignatureController extends Controller
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
        Log::info('Data from Mobile App - Signature: ' . json_encode($request->all()));
        $validated = $request->validate([
            'phone' => 'required|string',
            'signature' => 'nullable|string',
        ]);

        $staff = Staff::where('phone', $validated['phone'])->first();

                   // 
            $uploadedFiles = [];

            if ($request->has('signature')) {
                $uploadedFiles['signature'] = $this->processBase64File($request->signature, 'signature', $staff->id, ['jpeg', 'png', 'pdf']);
            }



            foreach ($uploadedFiles as $collection => $fileInfo) {
                $staff->addMedia($fileInfo['path'])
                    ->toMediaCollection($collection, 'staffs');
                    $staff->has_accepted_terms_and_conditions = 1;
                    $staff->save();
            }

        return response()->json(['success' => true, 'message' => 'Signature saved']);
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
