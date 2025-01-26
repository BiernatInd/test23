<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class settings extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'title', 'name', 'value'];

    /**
     * Get the value of a setting by its name.
     *
     * @param string $name
     * @return string|null
     */
    public static function getValueByName(string $name): ?string
    {
        $setting = self::where('name', $name)->first();
        return $setting ? $setting->value : null;
    }
}
