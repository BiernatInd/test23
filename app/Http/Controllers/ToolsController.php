<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laralabs\HereOAuth\Facade\HereOAuth;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


use Carbon\Carbon;


class ToolsController extends Controller
{
    public function requestTravelInfo($origins,$destinations,$latitude,$longitude)
    {

        Log::info('SENDINGREQUEST... '."ORIGINS".json_encode($origins)."DESTINATIONS:".json_encode($destinations));

        $token = HereOAuth::getToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json',
        ])->post('https://matrix.router.hereapi.com/v8/matrix?async=false', [
            'origins' => $origins,
            'destinations' => $destinations,
            'regionDefinition' => [
                'type' => 'circle',
                'center' => ["lat"=> (float) $latitude, "lng"=> (float) $longitude],
                'radius' => 200000,
            ],
            'matrixAttributes'=> ["distances","travelTimes"],
        ]);
    


        if ($response->successful()) {
            return $response;
        }else{
            Log::info($response);
            return 'error';
        }



    }
    // public function createNextStage($origins,$destinations,$latitude,$longitude){



        
    // }
}