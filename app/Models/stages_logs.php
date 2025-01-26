<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class stages_logs extends Model
{
    protected $table = 'stages_logs';

    protected $fillable = [
        'drivers_booking_uuid',
        'stage',
        'start_time',
        'end_time',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}
