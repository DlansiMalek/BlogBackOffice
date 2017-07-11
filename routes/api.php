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
//User API
Route::group(['prefix' => 'users'], function () {
    Route::get('', 'UserController@index');
    Route::get('{user_id}', 'UserController@getUserById');
    Route::put('{user_id}', 'UserController@update');
    Route::delete('{user_id}', 'UserController@delete');
    Route::post('register', 'UserController@register');
    Route::get('{user_id}/validate/{validation_code}', 'UserController@validateUser');
    Route::get('{user_id}/sendConfirmationEmail', 'UserController@resendConfirmationMail');
});

//Geo API
Route::group(['prefix' => 'geo'], function () {
    Route::group(['prefix' => 'countries'], function () {
        Route::get('', 'GeoController@getAllCountries');
        Route::get('{country_id}/cities', 'GeoController@getCitiesByCountry');
    });
    Route::group(['prefix' => 'cities'], function () {
        Route::get('', 'GeoController@getAllCities');
    });
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
