<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyHelp extends Model
{
        protected $fillable = [
        'phone',
        'latitude',
        'longitude',
        'notes',
        'description',
        'attended_by',
        ];
}
