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
    Route::group(['prefix' => '{session_stage_id}'], function () {
        Route::get('/', 'SessionStageController@getSessionStageById');
        Route::put('edit', 'SessionStageController@edit');
        Route::delete('delete', 'SessionStageController@delete');
    });
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
