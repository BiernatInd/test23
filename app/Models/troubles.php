<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class troubles extends Model
{
    protected $table = 'troubles';

    protected $fillable = [
        'stages_logs_id',
        'name',
        'time_value',
        'description',
        'latitude',
        'longitude',
    ];
    public function stageLog()
    {
        return $this->belongsTo(StageLog::class, 'stages_logs_id');
    }
}
