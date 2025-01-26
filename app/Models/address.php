<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class address extends Model
{
    protected $table = 'address';

    // Klucz główny tabeli
    protected $primaryKey = 'id';


    protected $fillable = [
        'id',
        'raw_address',
        'postal_code',
        'city',
        'longitude',
        'latitude',
    ];
    
}
