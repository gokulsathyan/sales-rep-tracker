<?php

use App\Http\Controllers\LocationLogController;
use App\Http\Controllers\StopReportingController;
use App\Http\Controllers\User\RegisterController;
use App\Http\Controllers\ZoneReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/save-location-log', [LocationLogController::class, 'saveLocationLog'])->middleware('auth:sanctum');
Route::get('/reps/{userId}/route', [LocationLogController::class, 'getLocationHistory']);
Route::get('/representatives', [RegisterController::class, 'getUserList']);
Route::get('/reps/{userId}/stop-report', [LocationLogController::class, 'getStopReport',]);
Route::post('/reports/zone', [LocationLogController::class, 'getZoneReport',]);

