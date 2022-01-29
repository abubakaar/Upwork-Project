<?php

use Illuminate\Http\Request;
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
//
//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::middleware('auth:sanctum')->group(function () {

    Route::post('profile', [\App\Http\Controllers\AuthenticationController::class, 'profile']);


});
Route::post('admin', [\App\Http\Controllers\AuthenticationController::class, 'adminSendMail']);
Route::post('login', [\App\Http\Controllers\AuthenticationController::class, 'login']);
Route::post('register', [\App\Http\Controllers\AuthenticationController::class, 'register']);
Route::post('confirmpin', [\App\Http\Controllers\AuthenticationController::class, 'confirmPin']);

