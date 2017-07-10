<?php

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'api'], function () {
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
});