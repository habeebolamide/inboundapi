<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



require "v1/auth.php";

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    require "v1/organization.php";
});