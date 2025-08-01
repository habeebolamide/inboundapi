<?php

use App\Http\Controllers\CheckInController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/sessions'], function () {
    Route::post('create', [CheckInController::class, 'create']);
    Route::post('supervisor_create', [CheckInController::class, 'Supervisorcreate']);
    Route::post('update', [CheckInController::class, 'update']);
    Route::get('get_sessions', [CheckInController::class, 'getAll']);
    Route::get('get_today_sessions', [CheckInController::class, 'getTodaySessions']);
    // Route::delete('delete/{id}', [CheckInController::class, 'delete']);

    Route::post('checkin', [CheckInController::class, 'checkIn']);
    Route::post('checkout', [CheckInController::class, 'checkOut']);
    Route::get('details', [CheckInController::class, 'getSessionDetails']);
});