<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileAppController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\PacjentRecevierController;
use App\Http\Controllers\PacjentSenderController;
use App\Http\Controllers\TestController;


Route::get('/', function () {
    return ['Laravel' => app()->version()];
});



// Mobile APP
    Route::post('/test', [MobileAppController::class, 'test']);
    Route::get('/available-booking', [MobileAppController::class, 'availableBooking']);
    // Route::post('/all-bookings', [MobileAppController::class, 'allBookings']);
    Route::get('/next-stage', [MobileAppController::class, 'nextStage']);
    Route::get('/request-booking', [MobileAppController::class, 'requestBooking']);
    Route::get('/cancel-booking', [MobileAppController::class, 'cancelBooking']);
    Route::post('/auth/login', [MobileAppController::class, 'login']);
    
    
    //  przyszlosciowo
    // Route::post('/test', [MobileAppController::class, 'reportTrouble']);
    // Route::post('/test', [MobileAppController::class, 'reportCriticalFailure']);
    // Route::post('/test', [MobileAppController::class, 'updateSettings']);
    // Route::post('/test', [MobileAppController::class, 'driverAvaible']);
    // Route::post('/test', [MobileAppController::class, 'driverNotAvaible']);



// Pacjent Recevier
    Route::post('/reserve-drive', [PacjentRecevierController::class, 'reserveDrive']);



// Pacjent Sender


// Testowe

    // Route::get('/test', [TestController::class, 'test']);

