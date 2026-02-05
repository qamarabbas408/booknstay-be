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

// Protected Admin Routes
Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsSuperAdmin::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/vendor/event', [VendorEventController::class, 'store']);

    Route::get('vendor/events', [VendorEventController::class, 'index']);
    Route::put('vendor/event/edit/{event}', [VendorEventController::class, 'update']); //edit 
    Route::delete('vendor/event/{event}', [VendorEventController::class, 'destroy']);
    Route::get('vendor/event/{event}', [VendorEventController::class, 'show']); // show single event by id 
  
    Route::get('guest/bookings', [BookingController::class, 'index']);
    Route::post('guest/event/booking', [BookingController::class, 'storeEventBooking']);
});

Route::post('/register/guest', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/interests', [InterestController::class, 'index']);

Route::post('/register/vendor', [RegisterController::class, 'registerVendor']);

// Public route - anyone can see hotels
Route::get('/hotels', [PublicHotelController::class, 'index']);

// Public route - anyone can see amenities
Route::get('/amenities', [AmentiyController::class, 'index']);

Route::get('/events', [PublicEventController::class, 'index']);
Route::get('/event-categories', [PublicEventController::class, 'getCategories']);
Route::get('/event/{event}', [PublicEventController::class, 'show']);
