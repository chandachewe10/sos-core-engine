<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
    }

    public function broadcastOn()
    {
        // Send to staff-specific channel
        return new PrivateChannel('emergency-staff-' . $this->staff->id);
    }

    public function broadcastAs()
    {
        return 'emergency-alert';
    }

    public function broadcastWith()
    {
        return [
            'victim_phone' => $this->emergency->phone,
            'latitude' => $this->emergency->latitude,
            'longitude' => $this->emergency->longitude,
            'distance_km' => round($this->distance, 2),
            'emergency_id' => $this->emergency->id,
            'timestamp' => now()->toISOString(),
            'message' => '🚨 EMERGENCY: Immediate assistance required!',
            'vibration_pattern' => 'sos' // Indicate unique vibration
        ];
    }
}