<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contact_data extends Model
{
    protected $table = 'contact_data';

    protected $primaryKey = 'drivers_booking_uuid';

    protected $keyType = 'string';

    // Pola, które można masowo przypisać
    protected $fillable = [
        'drivers_booking_uuid',
        'pacjent_phone_number',
        'pacjent_first_name',
        'pacjent_last_name',
        'opiekun_phone_number',
        'opiekun_first_name',
        'opiekun_last_name',
    ];

    protected $casts = [
        'drivers_booking_uuid' => 'string',
    ];
}
