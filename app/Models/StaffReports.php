<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffReports extends Model
{
    protected $fillable = [
        'staff_id',
        'case_id',
        'description',
        'severity',
        'outcome'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the staff member that owns the report
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the emergency case associated with this report
     */
    public function emergencyCase()
    {
        return $this->belongsTo(EmergencyHelp::class, 'case_id', 'id');
    }
}
