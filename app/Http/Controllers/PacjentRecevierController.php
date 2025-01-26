<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laralabs\HereOAuth\Facade\HereOAuth;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\drivers;
use App\Models\phones;
use App\Models\facility;
use App\Models\address;
use App\Models\contact_data;
use App\Models\drivers_booking;
use App\Models\current_position;
use App\Models\settings;
use App\Models\troubles;
use App\Models\stages_logs;



use Carbon\Carbon;

use App\Http\Controllers\ToolsController;

class PacjentRecevierController extends Controller
{
    public function reserveDrive(Request $request)
    {
        Log::info("HEJ");

        // Logowanie przychodzących danych
            Log::info('Received parameters:', $request->all());
            // Pobranie danych z requesta
            $data = $request->all();
            Log::info('Numer telefonu pacjenta: ' . $data['pacjent']['phoneNumber']);

            DB::beginTransaction();
            try {

                // Tworzenie nowego wpisu w tabeli address dla miejsca pacjenta
                $patientAddress = address::create([
                    'raw_address' => $data['pacjent']['rawAdress'],
                    'postal_code' => '00-000',
                    'city' => 'Miasto',
                    'latitude' => $data['pacjent']['latitude'],
                    'longitude' => $data['pacjent']['longitude'],
                ]);

                // Tworzenie nowego wpisu w tabeli address dla miejsca placówki
                $facilityAddress = address::create([
                    'raw_address' => $data['facility']['rawAdress'],
                    'postal_code' => '00-000', 
                    'city' => 'Miasto', 
                    'latitude' => $data['facility']['latitude'],
                    'longitude' => $data['facility']['longitude'],
                ]);

                // Tworzenie nowego wpisu w tabeli drivers_booking
                $driverBooking = drivers_booking::create([
                    'uuid' => $data['uuid'],
                    'drivers_uuid' => null, 
                    'distance' => 100, 
                    'travel_time' => 30, 
                    'visit_date_start' => $data['visit']['Date_Start'],
                    'visit_date_end' => $data['visit']['Date_end'],
                    'patient_address_id' => $patientAddress->id,
                    'facility_name' => $data['facility']['name'],
                    'facility_address_id' => $facilityAddress->id,
                    'additional_info' => json_encode($data['pacjent']['additionalInfo']),
                    'comment' => $data['pacjent']['comment'],
                    'important' => false, 
                    'booking_travel_status' => 'waiting_for_driver',
                    'editing_status' => 'new',
                ]);

                

                // Tworzenie nowego wpisu w tabeli contact_data
                contact_data::create([
                    'drivers_booking_uuid' => $driverBooking->uuid,
                    'patient_phone_number' => $data['pacjent']['phoneNumber'],
                    'patient_first_name' => $data['pacjent']['firstName'],
                    'patient_last_name' => $data['pacjent']['lastName'],
                    'opiekun_phone_number' => $data['user']['phoneNumber'],
                    'opiekun_first_name' => $data['user']['firstName'],
                    'opiekun_last_name' => $data['user']['lastName'],
                ]);
                DB::commit(); 
            } catch (\Exception $e) {
                DB::rollBack(); 
                Log::info("");
                Log::info("");
                Log::error($e);
                return response()->json(['message' => 'CRITICAL ERROR'], 501);
        
            }



        return response()->json(['message' => 'Success!'], 200);
    }
}
