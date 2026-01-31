<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\PublicHotelController;
use App\Http\Controllers\Api\AmentiyController;
use App\Http\Controllers\Api\PublicEventController;

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

    // Put all your Admin APIs here
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