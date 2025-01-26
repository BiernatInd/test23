<?php

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


use Carbon\Carbon;

use App\Http\Controllers\ToolsController;

class TestController extends Controller
{
    public function test(Request $request)
    {

        return response()->json(['message' => 'Success!'], 200);
    }
}
