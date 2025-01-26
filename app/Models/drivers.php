<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class drivers extends Model
{
    protected $table = 'drivers';

    // Klucz główny tabeli
    protected $primaryKey = 'uuid';


    protected $fillable = [
        'uuid',
        'basic_user_data',
        'phone_number',
        'address_id',
        'phones_id',
        'cars_id',
        'status',
    ];
    protected $casts = [
        'uuid' => 'string',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(phones::class,'phones_id');
    }


    public function car(): BelongsTo
    {
        return $this->belongsTo(cars::class,'cars_id');
    }


    public function currentPosition(): HasOne
    {
        return $this->hasOne(current_position::class, 'drivers_uuid', 'uuid');
    }
    public function getAddress()
    {
        return $this->belongsTo(address::class, 'address_id');
    }

}
