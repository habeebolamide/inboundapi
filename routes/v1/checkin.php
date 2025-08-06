<?php

use App\Http\Controllers\CheckInController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/sessions'], function () {
    Route::post('create', [CheckInController::class, 'create']);
    Route::post('supervisor_create', [CheckInController::class, 'Supervisorcreate']);
    Route::post('update', [CheckInController::class, 'update']);
    Route::get('get_sessions', [CheckInController::class, 'getAll']);
    Route::get('get_sessions_for_supervisors', [CheckInController::class, 'getAllSessioForSupervisor']);
    Route::get('get_today_sessions', [CheckInController::class, 'getTodaySessions']);
    Route::post('end_session', [CheckInController::class, 'endSession']);
    Route::post('start_session', [CheckInController::class, 'startSession']);
    Route::post('checkin', [CheckInController::class, 'checkIn']);
    Route::post('checkout', [CheckInController::class, 'checkOut']);
    Route::get('details', [CheckInController::class, 'getSessionDetails']);
});