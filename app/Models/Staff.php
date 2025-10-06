<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    

    protected $fillable = [
        'phone',
        'full_name',
        'email',
        'address',
        'hpcz_number',
        'nrc_uri',
        'selfie_uri',
        'signature_uri',
        'is_approved',
    ];
    
}
