<?php

use App\Http\Controllers\StravaAthleteController;
use App\Http\Controllers\StravaAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/strava-login', [StravaAuthController::class, 'login']);
Route::get('/strava-exchange-token', [StravaAuthController::class, 'exchangeToken']);

Route::get('/athletes/{athleteId}', [StravaAthleteController::class, 'show']);
Route::patch('/athletes/{athleteId}', [StravaAthleteController::class, 'update']);
