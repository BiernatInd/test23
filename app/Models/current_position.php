<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;


class current_position extends Model
{
    protected $table = 'current_position';

    // Klucz główny tabeli
    protected $primaryKey = 'drivers_uuid';


    protected $fillable = [
        'drivers_uuid',
        'latitude',
        'longitude',
    ];
    // public function driver()
    // {
    //     return $this->belongsTo(drivers::class, 'drivers_uuid', 'uuid');
    // }
}
