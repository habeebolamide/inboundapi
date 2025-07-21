<?php

use App\Http\Controllers\CheckInController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/checkin'], function () {
    Route::post('create', [CheckInController::class, 'create']);
    Route::post('update', [CheckInController::class, 'update']);
    Route::get('get_sessions', [CheckInController::class, 'getAll']);
    Route::delete('delete/{id}', [CheckInController::class, 'delete']);
});