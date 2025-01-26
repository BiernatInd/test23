<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class phones extends Model
{
    
    protected $table = 'phones';

    // Klucz główny tabeli
    protected $primaryKey = 'id';


    protected $fillable = [
        'imei',
        'model',
        'description',
    ];

    public function driver(): HasOne
    {
        return $this->hasOne(drivers::class);
    }
}
