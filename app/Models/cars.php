<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cars extends Model
{

    protected $table = 'cars';

    // Klucz główny tabeli
    protected $primaryKey = 'id';


    protected $fillable = [
        'registration_number',
        'brand',
        'color',
    ];

    public function driver(): HasOne
    {
        return $this->hasOne(drivers::class);
    }
}
