<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\OrganizationController;
use App\Http\Middleware\CheckIfAdmin;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/groups'], function () {
    Route::post('create', [GroupController::class, 'create'])->middleware(CheckIfAdmin::class);
    Route::get('getAll', [GroupController::class, 'getAll'])->middleware(CheckIfAdmin::class);
    Route::get('get_org_groups', [GroupController::class, 'getOrgGroups']); //->middleware(CheckIfAdmin::class);
});