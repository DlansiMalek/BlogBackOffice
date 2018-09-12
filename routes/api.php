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
Route::get('/grade/all', 'SharedController@getAllGrades');
Route::get('/lieu/all', 'SharedController@getAllLieux');

//Mobile API
Route::group(['prefix' => 'mobile'], function () {
    Route::post('/login', 'Auth\LoginController@loginAdminMobile');
    Route::group(['middleware' => 'organisateur'], function () {
        Route::get('/congress', 'CongressController@getCongressByAdmin');
        Route::group(['prefix' => 'scan'], function () {
            Route::post('participant', 'AdminController@scanParticipatorQrCode');
        });
        Route::group(['prefix' => 'presence/{id_Participator}'], function () {
            Route::post('status/update', 'AdminController@makeUserPresentCongress');
            Route::post('status/update/access', 'AdminController@makeUserPresentAccess');
        });
    });
});


Route::post('auth/login/admin', 'Auth\LoginController@loginAdmin');


Route::get('/testImpression', 'UserController@testImpression');
//User API
Route::group(['prefix' => 'users'], function () {
    Route::get('', 'UserController@index');
    Route::post('register', 'UserController@register');
    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('', 'UserController@getUserById');
        Route::put('', 'UserController@update');
        Route::delete('', 'UserController@delete');
        Route::get('validate/{validation_code}', 'UserController@validateUser');
        Route::get('sendConfirmationEmail', 'UserController@resendConfirmationMail');
        Route::get('sendingMailWithAttachement', 'UserController@sendingMailWithAttachement');
    });


});

//Congress API
Route::group(['prefix' => 'congress'], function () {
    Route::group(['prefix' => '{congress_id}'], function () {
        Route::get('', 'CongressController@getCongressById');
        Route::get('/eliminateInscription', 'AdminController@eliminateInscription');
        Route::get('badges', 'CongressController@getBadgesByCongress');
        Route::post('badge/upload', 'BadgeController@uploadBadgeToCongress');

        Route::post('badge/affect', 'BadgeController@affectBadgeToCongress');
        Route::post('badge/valider', 'BadgeController@validerBadge');
        Route::get('badge/apercu', 'BadgeController@apercuBadge');
    });
});
//User API
Route::group(['prefix' => 'user'], function () {

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');


    Route::group(['prefix' => 'congress'], function () {
        Route::group(['prefix' => '{congress_id}'], function () {
            Route::get('list', 'UserController@getUsersByCongress');
            Route::post('add', 'UserController@addUserToCongress');
            Route::post('register', 'UserController@registerUserToCongress');
            Route::get('presence/list', 'UserController@getPresencesByCongress');
        });

    });
    Route::group(['prefix' => 'access'], function () {
        Route::group(['prefix' => '{access_id}'], function () {
            Route::get('list', 'UserController@getUsersByAccess');
            Route::get('presence/list', 'UserController@getPresencesByAccess');
        });


    });
});
//Admin API
Route::group(['prefix' => 'admin', "middelware" => "super-admin"], function () {

    Route::group(['prefix' => 'me'], function () {
        Route::get('', 'AdminController@getAuhentificatedAdmin');
        Route::get('congress', 'AdminController@getAdminCongresses');
        Route::group(['prefix' => 'personels'], function () {
            Route::get('list', 'AdminController@getListPersonels');
            Route::post('add', 'AdminController@addPersonnel');
            Route::delete('{admin_id}/delete', 'AdminController@deletePersonnel');
            Route::get('{admin_id}/qr-code', 'AdminController@downloadQrCode');
        });
        Route::group(['prefix' => 'congress'], function () {
            Route::post('add', 'CongressController@addCongress');
            Route::post('{congressId}/edit', 'CongressController@editCongress');
        });
    });
    Route::group(['prefix' => 'qrcode'], function () {
        Route::post('scan', 'AdminController@scanParticipatorQrCode');
    });
    Route::group(['prefix' => 'participator'], function () {

        Route::group(['prefix' => '{congressId}'], function () {
            Route::get('all', 'AdminController@getAllParticipantsByCongress');
            Route::get('presence', 'AdminController@getAllPresenceByCongress');
        });
        Route::group(['prefix' => '{id_Participator}'], function () {
            Route::post('status/update', 'AdminController@makeUserPresentCongress');
            Route::post('status/update/access', 'AdminController@makeUserPresentAccess');
            Route::post('paied-status', 'AdminController@updatePaiedParticipator');
        });
    });

});
//Additional Info API
Route::group(['prefix' => 'add-info'], function () {

    Route::group(['prefix' => 'type'], function () {
        Route::get('list', 'AddInfoController@getAllTypesInfo');
    });

});
//Access API
Route::group(['prefix' => 'access'], function () {
    Route::group(['prefix' => 'congress'], function () {
        Route::get('{congress_id}/list', 'AccessController@getAllAccessByCongress');
    });

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
Route::get('updateUserWithCongress', 'AdminController@updateUserWithCongress');
Route::get('generateBadges/{userPos}', 'AdminController@generateBadges');
Route::get('generateTickets', 'AdminController@generateTickets');
Route::get('updateUsers', 'AdminController@updateUsers');
Route::get('generateUserQrCode', 'AdminController@generateUserQrCode');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'payement'], function () {
    Route::get('/types', 'UserController@getAllPayementTypes');
});
