<?php

use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/organization'], function () {
    Route::post('create', [OrganizationController::class, 'create'])->withoutMiddleware(['auth:sanctum']);;
});