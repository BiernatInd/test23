<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notifications extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'drivers_booking_uuid';

    public $incrementing = true;

    protected $keyType = 'int';


    public $timestamps = false;

    protected $fillable = [
        'drivers_booking_uuid',
        'notified_drivers_within_50km',
        'notified_drivers_within_100km',
    ];

    protected $casts = [
        'drivers_booking_uuid' => 'string',
    ];

}
