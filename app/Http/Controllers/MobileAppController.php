<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laralabs\HereOAuth\Facade\HereOAuth;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;

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

class MobileAppController extends Controller
{
    public function test(Request $request)
    {

        $token = $request->bearerToken();

        try {
            $user = JWTAuth::parseToken()->authenticate($token);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Nieprawidłowy token'], 401);
        }

        $driverRecord = DB::table('drivers')
        ->where('basic_user_data', $user->id)
        ->first();

        $userUUID = $driverRecord->uuid;



        $page = $request->input('page', 1);

        if($userUUID){
            $driver = drivers::where('uuid', $userUUID)->first();
            if (!$driver) {
                return response()->json(['message' => 'NO USER / ACCESS'], 419);
            }

            $latitude = $driver->getAddress->latitude;
            $longitude = $driver->getAddress->longitude;
        } else {

            return response()->json(['message' => 'NO USER UUID'], 419);


        }

        $closerDistance = settings::getValueByName('CLOSER_DISTANCE_DRIVERS');
        $longerDistance = settings::getValueByName('FURTHER_DISTANCE_DRIVERS');
        $minimalNumberOfDrivers = settings::getValueByName('MINIMAL_NUMBER_OF_DRIVERS_WITHIN_50KM');

        $selectedDate = $request->input('date');
        $selectedDate = Carbon::parse($selectedDate)->toDateString(); // Upewnij się, że jest w formacie 'YYYY-MM-DD'


        // Jeśli jest 'uuid', filtrujemy po tym, jeśli nie, to sprawdzamy tylko te z 'drivers_uuid' null
        $myBookingsQuery = drivers_booking::whereDate('visit_date_start', '=', $selectedDate);

        if ($userUUID) {
            $myBookingsQuery->where('drivers_uuid', '=', $userUUID);
        } else {
            $myBookingsQuery->whereNull('drivers_uuid');
        }

        $myBookings = $myBookingsQuery->paginate(5, ['*'], 'page', $page);

        $allBookings = $myBookings;

        $destinations = [];
        $origins = [];

        $origins[] = [
            'lat' => (float) $latitude,
            'lng' => (float) $longitude,
        ];

        foreach ($allBookings as $booking) {
            $destinations[] = [
                'lat' => (float) $booking->patientAddress->latitude,
                'lng' => (float) $booking->patientAddress->longitude,
            ];
        }
        $toolsController = new ToolsController();
        $response = $toolsController->requestTravelInfo($origins,$destinations,$latitude,$longitude);

        // KOD DO DODANIA FUNKCJONALNOSCI LOKALIZACJI
            $currentLatitude = $request->input('currentLatitude');
            $currentLongitude = $request->input('currentLongitude');

            $origins = [];
            if($currentLatitude && $currentLongitude ){

            $origins[] = [
                    'lat' => (float) $currentLatitude,
                    'lng' => (float) $currentLongitude,
                ];
            $response2 = $toolsController->requestTravelInfo($origins,$destinations,$currentLatitude,$currentLongitude);
        }

        if ($response!='error' && $response2!='error') {
        // if ($response!='error') {

            $data = json_decode($response, true);
            $travelTimes = $data['matrix']['travelTimes'];
            $distances = $data['matrix']['distances'];

            $data2 = json_decode($response2, true);
            $travelTimes2 = $data2['matrix']['travelTimes'];
            $distances2 = $data2['matrix']['distances'];

            $i = 0;
            foreach ($allBookings as $booking) {

                $booking->facility_address = $booking->facilityAddress;
                $booking->home_to_patient_travel_time = $travelTimes[$i];
                $booking->home_to_patient_travel_distance = $distances[$i];

                $booking->myPos_to_patient_travel_time = $travelTimes2[$i];
                $booking->myPos_to_patient_travel_distance = $distances2[$i];


                $booking->contact_data = $booking->contactData;


                unset($booking->contact_data->drivers_booking_uuid);
                unset($booking->contact_data->updated_at);
                unset($booking->contact_data->created_at);

                // z jakiego dupska to jest to ja nie mam pojecia
                // unset($booking->patient_address->updated_at);
                // unset($booking->patient_address->created_at);

                unset($booking->notifications);
                unset($booking->editing_status);
                unset($booking->updated_at);
                unset($booking->booking_travel_status);
                unset($booking->patient_address_id);
                unset($booking->facility_address_id);


                $i++;
            }

        } else {
            return response()->json([
                'message' => 'ERROR FETCHING DATA!',
            ], 404);
        }

        // Log::info($allBookings);

        return response()->json([
            'message' => 'Success!',
        'Bookings' => $myBookings->items(),
            'current_page' => $myBookings->currentPage(),
            'total_pages' => $myBookings->lastPage(),
            'total_records' => $myBookings->total(),
        ], 200);
    }


    public function availableBooking(Request $request)
    {
        $token = $request->bearerToken();

        try {
            $user = JWTAuth::parseToken()->authenticate($token);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Nieprawidłowy token'], 401);
        }

        $driverRecord = DB::table('drivers')
        ->where('basic_user_data', $user->id)
        ->first();

        $userUUID = $driverRecord->uuid;

        $page = $request->input('page', 1);

        if($userUUID){
            $driver = drivers::where('uuid', $userUUID)->first();
            if (!$driver) {
                return response()->json(['message' => 'NO USER / ACCESS'], 419);
            }

            $latitude = $driver->getAddress->latitude;
            $longitude = $driver->getAddress->longitude;
        } else {

            return response()->json(['message' => 'NO USER UUID'], 419);
        }

        $closerDistance = settings::getValueByName('CLOSER_DISTANCE_DRIVERS');
        $longerDistance = settings::getValueByName('FURTHER_DISTANCE_DRIVERS');
        $minimalNumberOfDrivers = settings::getValueByName('MINIMAL_NUMBER_OF_DRIVERS_WITHIN_50KM');

        $selectedDate = $request->input('date');
        // Jeśli jest 'uuid', filtrujemy po tym, jeśli nie, to sprawdzamy tylko te z 'drivers_uuid' null
        $closerBookings = drivers_booking::join('address as a', 'drivers_booking.patient_address_id', '=', 'a.id')
            ->select('drivers_booking.*')
            ->whereRaw(
                'drivers_booking.drivers_uuid is null AND drivers_booking.visit_date_start >= CURRENT_DATE AND ((
                    6371 * acos(
                        cos(radians(?)) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(a.latitude))
                    )
                ) < ?)', [$latitude, $longitude, $latitude, $closerDistance]
            )
            ->whereDate('drivers_booking.visit_date_start', '=', $selectedDate)
            ->paginate(5, ['*'], 'page', $page);

        $furtheBbookings = drivers_booking::join('address as a', 'drivers_booking.patient_address_id', '=', 'a.id')
            ->join('notifications as n', 'drivers_booking.uuid', '=', 'n.drivers_booking_uuid')
            ->select('drivers_booking.*')
            ->whereRaw(
                'drivers_booking.drivers_uuid is null
                AND drivers_booking.visit_date_start >= CURRENT_DATE
                AND (
                    (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(a.latitude))
                        )
                    ) BETWEEN ? AND ?
                    AND (n.notified_drivers_within_50km <= ? OR drivers_booking.important)
                )',
                [
                    $latitude, $longitude, $latitude, $closerDistance, $longerDistance, $minimalNumberOfDrivers
                ]
            )
            ->whereDate('drivers_booking.visit_date_start', '=', $selectedDate)
            ->paginate(5, ['*'], 'page', $page);

        $allBookings = $closerBookings->merge($furtheBbookings);


        if ($allBookings->isEmpty()) {
            return response()->json([
                'message' => 'no bookings!',
            ], 404);
        }

        $destinations = [];
        $origins = [];

        $origins[] = [
            'lat' => (float) $latitude,
            'lng' => (float) $longitude,
        ];

        // incremet notifications
        // foreach ($closerBookings as $closerBooking) {
        //     $closerBooking->incrementNotifications50();
        // }
        // foreach ($furtheBbookings as $furtheBooking) {
        //     $furtheBooking->incrementNotifications100();
        // }

        foreach ($allBookings as $booking) {
            $destinations[] = [
                'lat' => (float) $booking->patientAddress->latitude,
                'lng' => (float) $booking->patientAddress->longitude,
            ];
        }
        $toolsController = new ToolsController();
        $response = $toolsController->requestTravelInfo($origins,$destinations,$latitude,$longitude);

        // KOD DO DODANIA FUNKCJONALNOSCI LOKALIZACJI
            $currentLatitude = $request->input('currentLatitude');
            $currentLongitude = $request->input('currentLongitude');

            $origins = [];
            if($currentLatitude && $currentLongitude ){

            $origins[] = [
                    'lat' => (float) $currentLatitude,
                    'lng' => (float) $currentLongitude,
                ];
            $response2 = $toolsController->requestTravelInfo($origins,$destinations,$currentLatitude,$currentLongitude);
        }

        if ($response!='error' && $response2!='error') {
        // if ($response!='error') {

            $data = json_decode($response, true);
            $travelTimes = $data['matrix']['travelTimes'];
            $distances = $data['matrix']['distances'];

            $data2 = json_decode($response2, true);
            $travelTimes2 = $data2['matrix']['travelTimes'];
            $distances2 = $data2['matrix']['distances'];

            $i = 0;
            foreach ($allBookings as $booking) {

                $booking->facility_address = $booking->facilityAddress;
                $booking->home_to_patient_travel_time = $travelTimes[$i];
                $booking->home_to_patient_travel_distance = $distances[$i];

                $booking->myPos_to_patient_travel_time = $travelTimes2[$i];
                $booking->myPos_to_patient_travel_distance = $distances2[$i];


                $booking->contact_data = $booking->contactData;


                unset($booking->contact_data->drivers_booking_uuid);
                unset($booking->contact_data->updated_at);
                unset($booking->contact_data->created_at);

                // z jakiego dupska to jest to ja nie mam pojecia
                // unset($booking->patient_address->updated_at);
                // unset($booking->patient_address->created_at);

                unset($booking->notifications);
                unset($booking->editing_status);
                unset($booking->updated_at);
                unset($booking->booking_travel_status);
                unset($booking->patient_address_id);
                unset($booking->facility_address_id);


                $i++;
            }

        } else {
            return response()->json([
                'message' => 'ERROR FETCHING DATA!',
            ], 404);
        }

        return response()->json([
            'message' => 'Success!',
            'Bookings' => $allBookings,
            'current_page' => max($closerBookings->currentPage(), $furtheBbookings->currentPage()), // Compare current pages correctly
            'total_pages' => max($closerBookings->lastPage(), $furtheBbookings->lastPage()), // Use lastPage for total pages
            'total_records' => max($closerBookings->total(), $furtheBbookings->total()), // Compare total records correctly
        ], 200);

    }

    public function requestBooking(Request $request)
    {    $token = $request->bearerToken();

        try {
            $user = JWTAuth::parseToken()->authenticate($token);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Nieprawidłowy token'], 401);
        }

        $driverRecord = DB::table('drivers')
        ->where('basic_user_data', $user->id)
        ->first();

        $userUUID = $driverRecord->uuid;

        $bookinguuid = $request->input('bookinguuid');
        $booking = drivers_booking::where('uuid', '=', $bookinguuid)->first();
        if($booking == null || $booking->drivers_uuid != null){


        return response()->json(['message' => 'This Booking might have been taken'], 410);

        }else{
            $booking->drivers_uuid=$userUUID;
            $booking->booking_travel_status="driver_found";
            $booking->save();
        }

        return response()->json(['message' => 'Success!'], 200);
    }
    public function cancelBooking(Request $request)
    {
        $token = $request->bearerToken();

        try {
            $user = JWTAuth::parseToken()->authenticate($token);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Nieprawidłowy token'], 401);
        }

        $driverRecord = DB::table('drivers')
        ->where('basic_user_data', $user->id)
        ->first();

        $userUUID = $driverRecord->uuid;

        $bookinguuid = $request->input('bookinguuid');
        $booking = drivers_booking::where('uuid', '=', $bookinguuid)->first();
        if($booking == null || $booking->drivers_uuid != $userUUID){

        return response()->json(['message' => 'You dont have this booking or it doesnt exist'], 410);

        }else{
            $booking->drivers_uuid=null;
            $booking->booking_travel_status="driver_cancelled";
            $booking->save();
        }

        return response()->json(['message' => 'Success!'], 200);
    }
    public function nextStage(Request $request)
    {

        $token = $request->bearerToken();

        Log::info('Otrzymany token Bearer z requestu 1:', ['token' => $token]);

        try {
            $user = JWTAuth::parseToken()->authenticate($token);

            Log::info('Otrzymany cos zebysmy wiedzieli Bearer z requestu 1:', ['uuid' => $user->uuid]);
            Log::info('Otrzymany cos zebysmy wiedzieli 2 requestu 1:', ['id' => $user->id]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Nieprawidłowy token'], 401);
        }

        $driverRecord = DB::table('drivers')
        ->where('basic_user_data', $user->id)
        ->first();

        $userUUID = $driverRecord->uuid;

        $bookinguuid = $request->input('bookinguuid');
        $latitude = $request->input('currentLatitude');
        $longitude = $request->input('currentLongitude');




        $lastStageLog = stages_logs::where('drivers_booking_uuid', $bookinguuid)
        ->orderBy('start_time', 'desc')
        ->first();

            // Sprawdzenie, czy znaleziono jakikolwiek rekord
            if ($lastStageLog) {
            // Ustawienie end_time w ostatnim etapie
            $lastStageLog->end_time = Carbon::now();
            $lastStageLog->save();
            }else{
                $booking = drivers_booking::where('uuid', '=', $bookinguuid)->first();
                if($booking==null){
                    return response()->json(['message' => 'There is no booking with that uuid!'], 400);
                }

                $newStageLog = new stages_logs();
                $newStageLog->drivers_booking_uuid = $bookinguuid;
                $newStageLog->stage = 'drivers_home_to_patient';
                $newStageLog->start_time = Carbon::now();
                $newStageLog->latitude = $latitude;
                $newStageLog->longitude = $longitude;
                $newStageLog->save();
                return response()->json(['message' => 'Success!'], 200);
            }

            // Określenie kolejnego etapu (stage)
            // tutaj trzeba sie zastanowic jak to bedzie wygladalo w aplikacji kierowcy!
            $stages = [
            'drivers_home_to_patient',
            'waiting_at_pacjent_home',


            'drive_to_facility',
            'waiting_in_facility',


            'facility_to_patient_home',
            'ended'
            ];

            $currentStageIndex = array_search($lastStageLog->stage, $stages);

            $nextStage = isset($stages[$currentStageIndex + 1]) ? $stages[$currentStageIndex + 1] : null;

            if (!$nextStage) {
            return response()->json(['message' => 'Drive ended'], 402);
            }

            $newStageLog = new stages_logs();
            $newStageLog->drivers_booking_uuid = $bookinguuid;
            $newStageLog->stage = $nextStage;
            $newStageLog->start_time = Carbon::now();
                if($nextStage =='ended'){
                $newStageLog->end_time = Carbon::now();
                }
            $newStageLog->latitude = $latitude;
            $newStageLog->longitude = $longitude;
            $newStageLog->save();

        return response()->json(['message' => 'Success!'], 200);
    }

    public function check_database(Request $request)
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            Log::info("Database connection successful.");


        } catch (\Exception $e) {
            Log::error("Could not connect to the database. Please check your configuration. Error: " . $e->getMessage());
            die("Could not connect to the database. Please check your configuration.");
        }

    }

    public function login(Request $request)
{
    Log::info('Otrzymane dane żądania logowania:', $request->all());

    // Walidacja danych wejściowych
    $validator = Validator::make($request->all(), [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // Logowanie błędów walidacji, jeśli występują
    if ($validator->fails()) {
        Log::error('Błąd walidacji:', $validator->errors()->toArray());

        return response()->json([
            'message' => 'Wprowadzone dane są nieprawidłowe',
            'errors' => $validator->errors()
        ], 200);
    }

    // Pobranie zwalidowanych danych
    $credentials = $validator->validated();
    Log::info('Zweryfikowane dane logowania:', $credentials);

    // Próba uwierzytelnienia użytkownika
    if (Auth::guard('web')->attempt($credentials)) {
        $request->session()->regenerate();

        // Pobranie danych użytkownika po pomyślnym uwierzytelnieniu
        $user = Auth::guard('web')->user();

        // Sprawdzenie, czy użytkownik ma rekord w tabeli drivers
        $driverRecord = DB::table('drivers')
            ->where('basic_user_data', $user->id)
            ->first();

        // Jeśli istnieje rekord, dodaj uuid do odpowiedzi
        $driverUuid = $driverRecord ? $driverRecord->uuid : null;

        if ($driverUuid) {
            Log::info('Rekord driver znaleziony:', [
                'user_id' => $user->id,
                'uuid' => $driverUuid,
            ]);
        } else {
            Log::warning('Brak rekordu driver dla użytkownika:', [
                'user_id' => $user->id,
            ]);
        }

        // Generowanie tokenu JWT
        $token = JWTAuth::fromUser($user, [
            'exp' => Carbon::now()->addHours(24)->timestamp,
            'id' => $user->id,
            'email' => $user->email,
            'uuid' => $driverUuid, 
        ]);

        Log::info('Wygenerowany token JWT:', ['token' => $token]);

        // Tworzenie ciasteczka z tokenem JWT
        $cookie = Cookie::make('jwt_token', $token, 1440, null, null, false, true);

        return response()->json([
            'message' => 'Zalogowano pomyślnie',
            'id' => $user->id,
            'email' => $user->email,
            'token' => $token,
        ])->withCookie($cookie);
    }

    // Logowanie nieudanej próby logowania
    Log::warning('Nieudana próba logowania dla adresu email:', ['email' => $request->input('email')]);

    return response()->json(['message' => 'Błędne dane logowania'], 401);
}




// WAŻNE

            // Pobierz rezerwację z jej adresem
            // $booking = facility_booking::all();

            // foreach($booking as $oneBooking){

            // Log::info("ADRES PACJENTA:".$oneBooking->pacjent_address_address->raw."  |  ADRES PLACOWKI: ".$oneBooking->facility_doctor->facility->facility_address->raw);
            // }

// HERE MAPS acces
        // API ID: ILzpq8FLChfheu55VCWp
        // API KEY: QJL72UMdEDxiUv2gPEzO6QQ1D3RZytHbDQbRThu8QFM
        // In this folder is included index.html wchich can decode polynome (route from heremaps content to coordintes)
        // usefull link: https://stackoverflow.com/questions/68829396/how-to-decode-polyline-from-here-routing-api-8
        // https://www.here.com/docs/bundle/routing-api-developer-guide-v8/page/tutorials/route-start-end-time.html
/*
        $response = Http::get('https://geocode.search.hereapi.com/v1/geocode', [
            'q' => 'Daromin 97A, Polska',
            'apiKey' => 'QJL72UMdEDxiUv2gPEzO6QQ1D3RZytHbDQbRThu8QFM',
        ]);

        $response2 = Http::get('https://geocode.search.hereapi.com/v1/geocode', [
            'q' => 'Warszawa',
            'apiKey' => 'QJL72UMdEDxiUv2gPEzO6QQ1D3RZytHbDQbRThu8QFM',
        ]);


        // Dekodowanie JSON-a na tablicę PHP
        $data = json_decode($response, true);
        $data2 = json_decode($response2, true);

        // Wydobywanie wartości "lat" i "lng" z odpowiednich kluczy
        $lat = $data['items'][0]['position']['lat'];
        $lng = $data['items'][0]['position']['lng'];

        $lat2 = $data2['items'][0]['position']['lat'];
        $lng2 = $data2['items'][0]['position']['lng'];


        $routeDuration = Http::get('https://router.hereapi.com/v8/routes', [
            'origin' => $lat .",". $lng,
            'destination' => $lat2.",". $lng2,
            'return' => 'polyline,summary,typicalDuration',
            'transportMode' => 'car',
            'apiKey' => 'QJL72UMdEDxiUv2gPEzO6QQ1D3RZytHbDQbRThu8QFM',
        ]);

    return "Hello World + $response <br><br><br>  $response2 <br><br><br>  $routeDuration  <br><br><br> Time: ";
*/

}
