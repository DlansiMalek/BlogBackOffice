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


//Shared API
Route::group(['prefix' => 'shared'], function () {
    Route::get('service', 'SharedController@getAllServices');
    Route::get('niveau', 'SharedController@getAllNiveaux');
});


//Session Stage API
Route::group(['prefix' => 'session-stage'], function () {
    Route::post('add', 'SessionStageController@addSessionStage');
    Route::get('', 'SessionStageController@getAllSessionStage');
    Route::get('{session_stage_id}', 'SessionStageController@getSessionStageById');
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
