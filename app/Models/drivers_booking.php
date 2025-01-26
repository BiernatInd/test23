<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\notifications;

class drivers_booking extends Model
{
    protected $table = 'drivers_booking';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'drivers_uuid',
        'distance',
        'travel_time',
        'visit_date_start',
        'visit_date_end',
        'patient_address_id',
        'facility_name',
        'facility_address_id',
        'additional_info',
        'comment',
        'important',
        'booking_travel_status',
        'editing_status',
    ];
    protected $casts = [
        'uuid' => 'string',
        'drivers_uuid' => 'string',
        'distance' => 'integer',
        'travel_time' => 'integer',
        'visit_date_start' => 'datetime',
        'visit_date_end' => 'datetime',
        'patient_address_id' => 'integer',
        'facility_address_id' => 'integer',
        'additional_info' => 'array',
        'important' => 'boolean',
    ];

    public function contactData()
    {
        return $this->hasOne(contact_data::class, 'drivers_booking_uuid', 'uuid');
    }

    public function driver()
    {
        return $this->belongsTo(driver::class, 'drivers_uuid', 'uuid');
    }


    public function facilityAddress()
    {
        return $this->belongsTo(address::class, 'facility_address_id');
    }
    public function patientAddress()
    {
        return $this->belongsTo(address::class, 'patient_address_id');
    }

    public function notification()
        {
            return $this->hasOne(notifications::class, 'drivers_booking_uuid', 'uuid');
        }
        // public function incrementNotifications50()
        // {
        //     $notification = $this->notification; 
        //     if ($notification) {
        //         $old = $notification->notified_drivers_within_50km;
        //         $notification->notified_drivers_within_50km = $old + 1;
        //         $notification->save();
        //     }
        // }
        
        // public function incrementNotifications100()
        // {
          
        //     $notification = $this->notification; 
        //     if ($notification) {
        //         $old = $notification->notified_drivers_within_100km;
        //         $notification->notified_drivers_within_100km = $old + 1;
        //         $notification->save();
        //     }
        // }

}
