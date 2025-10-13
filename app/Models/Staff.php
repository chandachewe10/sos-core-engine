<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Staff extends Model implements HasMedia
{
    use InteractsWithMedia;

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
        'has_accepted_terms_and_conditions',
        'last_known_latitude',
        'last_known_longitude',
       'fcm_token',
    ];
}
