<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// // Public login route
// Route::post('/login', [AuthController::class, 'login']);

// // Protected Admin Routes
// Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsSuperAdmin::class])->group(function () {
//     Route::get('/admin/dashboard', function () {
//         return response()->json(['message' => 'Welcome to the Admin Secret Vault']);
//     });

//     // Put all your Admin APIs here
// });
