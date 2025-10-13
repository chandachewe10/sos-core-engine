<?php

namespace App\Events;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmergencyAlertEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $staff;
    public $emergency;
    public $distance;
    public $staffUserId; 

    public function __construct($staff, $emergency, $distance)
    {
        $this->staff = $staff;
        $this->emergency = $emergency;
        $this->distance = $distance;

        // Get the user ID from the staff email
        $this->staffUserId = User::where('email', $staff->email)->first()->id;
        
        $channelName = 'public-emergency-' . $this->staffUserId; // Changed to public

        Log::info('🚨 EmergencyAlertEvent Created', [
            'staff_id' => $this->staff->id,
            'staff_user_id' => $this->staffUserId,
            'staff_name' => $staff->full_name,
            'staff_email' => $staff->email,
            'victim_phone' => $emergency->phone,
            'distance_km' => $distance,
            'channel' => $channelName
        ]);
    }

    public function broadcastOn()
    {
        $channelName = 'public-emergency-' . $this->staffUserId; // Changed to public
        
        Log::info('📡 Broadcasting to PUBLIC channel', [ // Updated log message
            'channel' => $channelName,
            'staff_id' => $this->staff->id,
            'staff_user_id' => $this->staffUserId
        ]);
        
        // Send to staff-specific PUBLIC channel
        return new Channel($channelName); // Changed from PrivateChannel to Channel
    }

    public function broadcastAs()
    {
        Log::info('🎯 Broadcasting as event: emergency-alert');
        return 'emergency-alert';
    }

    public function broadcastWith()
    {
        $data = [
            'victim_phone' => $this->emergency->phone,
            'latitude' => $this->emergency->latitude,
            'longitude' => $this->emergency->longitude,
            'distance_km' => round($this->distance, 2),
            'emergency_id' => $this->emergency->id,
            'timestamp' => now()->toISOString(),
            'message' => '🚨 EMERGENCY: Immediate assistance required!',
            'vibration_pattern' => 'sos' 
        ];
        
        Log::info('📦 Broadcast data prepared', $data);
        
        return $data;
    }
}