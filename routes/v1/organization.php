<?php

use App\Http\Controllers\OrganizationController;
use App\Http\Middleware\CheckIfAdmin;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/organization'], function () {
    Route::post('login', [OrganizationController::class, 'login'])->withoutMiddleware(['auth:sanctum']);
    Route::post('create', [OrganizationController::class, 'create'])->withoutMiddleware(['auth:sanctum']);
    Route::group(['prefix' => '/supervisors'], function () {
      Route::post('upload', [OrganizationController::class, 'CreateSupervisors'])->middleware(CheckIfAdmin::class);
      Route::get('getSupervisors', [OrganizationController::class, 'getSupervisors'])->middleware(CheckIfAdmin::class);
      Route::get('getOrganizationSupervisors', [OrganizationController::class, 'getOrganizationSupervisors'])->middleware(CheckIfAdmin::class);
    });
    require "group.php";
    require "checkin.php";
});