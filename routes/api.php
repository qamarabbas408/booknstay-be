<?php

use App\Http\Controllers\Api\AmentiyController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\PublicEventController;
use App\Http\Controllers\Api\PublicHotelController;
use App\Http\Controllers\Api\Vendor\VendorEventController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\V2\RegistrationController as V2RegistrationController;
use App\Http\Controllers\Api\Vendor\VendorStatusController;
use App\Http\Controllers\Api\Vendor\VendorHotelController;
use App\Http\Controllers\Api\Vendor\VendorRoomTypeController;
use App\Http\Controllers\Api\V2\BookingController as V2BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {
    // Protected Admin Routes

    Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsSuperAdmin::class])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        //EVENTS
        Route::post('/vendor/event', [VendorEventController::class, 'store']);
        Route::get('vendor/events', [VendorEventController::class, 'index']);
        Route::put('vendor/event/edit/{event}', [VendorEventController::class, 'update']); //edit
        Route::delete('vendor/event/{event}', [VendorEventController::class, 'destroy']);
        Route::get('vendor/event/{event}', [VendorEventController::class, 'show']); // show single event by id

        // HOTELS
        Route::get('vendor/hotels', [VendorHotelController::class, 'index']);
        Route::put('/vendor/hotels/{hotel}', [VendorHotelController::class, 'update']);
        Route::get('vendor/hotels/{hotel}', [VendorHotelController::class, 'show']);
        Route::delete('vendor/hotels/{hotel}', [VendorHotelController::class, 'destroy']);

        // BOOKINGS
        Route::get('guest/bookings', [BookingController::class, 'index']);
        Route::post('guest/event/booking', [BookingController::class, 'storeEventBooking']);
        Route::post('guest/hotel/booking', [BookingController::class, 'storeHotelBooking']);


        // ROOM TIERS
        Route::get('/hotels/{hotel}/room-types', [VendorRoomTypeController::class, 'index']);
        Route::post('/hotels/{hotel}/room-types', [VendorRoomTypeController::class, 'store']);
        Route::put('/room-types/{roomType}', [VendorRoomTypeController::class, 'update']);
        Route::delete('/room-types/{roomType}', [VendorRoomTypeController::class, 'destroy']);
        Route::get('/vendor/room-types/{roomType}', [VendorRoomTypeController::class, 'show']); //get one room

    });

    Route::middleware('auth:sanctum')->prefix('guest')->group(function () {
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
    });

//    PUBLIC ROUTES
    Route::post('/register/guest', [RegisterController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/interests', [InterestController::class, 'index']);

    Route::post('/register/vendor', [RegisterController::class, 'registerVendor']);

    // Public route - anyone can see hotels
    Route::get('/hotels', [PublicHotelController::class, 'index']);
    Route::get('/hotel/{hotel}', [PublicHotelController::class, 'show']);
    // routes/api.php
    Route::get('/hotel/{id}/availability', [PublicHotelController::class, 'checkAvailability']);

    // Public route - anyone can see amenities
    Route::get('/amenities', [AmentiyController::class, 'index']);

    Route::get('/events', [PublicEventController::class, 'index']);
    Route::get('/event-categories', [PublicEventController::class, 'getCategories']);
    Route::get('/event/{event}', action: [PublicEventController::class, 'show']);



});

//V2 api/v2
Route::prefix('v2')->group(function () {
    Route::post('/register/vendor', action: [V2RegistrationController::class, 'registerVendor']);

    Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsSuperAdmin::class])->group(function () {
    Route::get('/approval-status', action: [VendorStatusController::class, 'check']);
    Route::post('/vendor/hotel', action: [VendorHotelController::class, 'store']);
//    Route::post('guest/hotel/booking', [V2BookingController::class, 'storeHotelBooking']);
    });

    Route::middleware('auth:sanctum')->prefix('guest')->group(function () {
        Route::post('/hotel/booking', [V2BookingController::class, 'storeHotelBooking']);
    });

});
