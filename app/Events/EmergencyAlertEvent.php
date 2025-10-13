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

    public function __construct($staff, $emergency, $distance)
    {
        $this->staff = $staff;
        $this->emergency = $emergency;
        $this->distance = $distance;
        
        Log::info('🚨 EmergencyAlertEvent Created', [
            'staff_id' => $staff->id,
            'staff_name' => $staff->full_name,
            'victim_phone' => $emergency->phone,
            'distance_km' => $distance
        ]);
    }

    public function broadcastOn()
    {
        $staffEmail = Staff::findOrFail($this->staff->id);
        $staffUserId = User::where('email', $staffEmail->email)->first()->id;
        $channelName = 'emergency-staff-' . $staffUserId;
        
        Log::info('📡 Broadcasting to channel', [
            'channel' => $channelName,
            'staff_id' => $this->staff->id
        ]);
        
        // Send to staff-specific channel
        return new PrivateChannel($channelName);
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
            'vibration_pattern' => 'sos' // Indicate unique vibration
        ];
        
        Log::info('📦 Broadcast data prepared', $data);
        
        return $data;
    }
}