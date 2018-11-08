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
Route::get('/privileges', 'SharedController@getAllPrivileges');
Route::get('/countries', 'SharedController@getAllCountries');
Route::get('/types-attestation', 'SharedController@getAllTypesAttestation');
Route::get('/congress-logo/{path}', 'SharedController@getLogoCongress');
Route::get('/payement-user-recu/{path}', 'SharedController@getRecuPaiement');

//Mobile API
Route::group(['prefix' => 'mobile'], function () {
    Route::post('/login', 'Auth\LoginController@loginAdminMobile');
    Route::group(['middleware' => 'organisateur'], function () {
        Route::get('/congress', 'CongressController@getCongressByAdmin');
        Route::post('/start-access', 'AccessController@startAccessById');
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
        Route::delete('delete', 'UserController@delete');
        Route::post('upload-payement', 'UserController@uploadPayement');
        Route::get('validate/{validation_code}', 'UserController@validateUserAccount');
        Route::get('sendConfirmationEmail', 'UserController@resendConfirmationMail');
        Route::get('sendingMailWithAttachement', 'UserController@sendingMailWithAttachement');
        Route::put('change-paiement', 'UserController@changePaiement');
        Route::get('send-attestation-mail', 'UserController@sendMailAttesation');
    });


});

//Congress API
Route::group(['prefix' => 'congress', "middelware" => "jwt"], function () {
    Route::group(['prefix' => '{congress_id}'], function () {
        Route::get('', 'CongressController@getCongressById');
        Route::get('/eliminateInscription', 'AdminController@eliminateInscription');
        Route::get('/sendMailAllParticipants', 'AdminController@sendMailAllParticipants');
        Route::get('badges', 'CongressController@getBadgesByCongress');
        Route::post('badge/upload', 'BadgeController@uploadBadgeToCongress');

        Route::post('/upload-logo', 'CongressController@uploadLogo');
        Route::post('badge/affect', 'BadgeController@affectBadgeToCongress');
        Route::get('badge/apercu', 'BadgeController@apercuBadge');

        Route::group(['prefix' => 'attestation'], function () {
            Route::post('affect/{accessId}', 'BadgeController@affectAttestationToCongress')
                ->where('accessId', '[0-9]+');
            Route::post('affect/divers', 'BadgeController@affectAttestationDivers');
        });
        Route::group(['prefix' => 'invoices'], function () {
            Route::group(['prefix' => 'organization'], function () {
                Route::get('', 'CongressController@getLabsByCongress');
                Route::group(['prefix' => '{labId}'], function () {
                    Route::get('', 'CongressController@getOrganizationInvoice');
                });
            });
        });
    });
});
//User API
Route::group(['prefix' => 'user', "middelware" => "jwt"], function () {

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');


    Route::group(['prefix' => 'congress'], function () {
        Route::group(['prefix' => '{congress_id}'], function () {
            Route::get('list', 'UserController@getUsersByCongress');
            Route::post('list/privilege', 'UserController@getUsersByPrivilegeByCongress');
            Route::post('add', 'UserController@addUserToCongress');
            Route::post('register', 'UserController@registerUserToCongress');
            Route::put('edit', 'UserController@editerUserToCongress');
            Route::post('add-fast-user', 'UserController@addingFastUserToCongress');
            Route::put('edit-fast-user/{user_id}', 'UserController@editFastUserToCongress');
            Route::get('presence/list', 'UserController@getPresencesByCongress');
            Route::post('status-presence', 'UserController@getUserStatusPresences');

            Route::post('save-excel', 'UserController@saveUsersFromExcel');
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
    Route::group(['prefix' => 'rfid'], function () {
        Route::post('user/{userId}/update', 'AdminController@updateUserRfid');
        Route::post('user/attestations', 'AdminController@getAttestationByUserRfid');

    });
    Route::group(['prefix' => 'me'], function () {
        Route::get('', 'AdminController@getAuhentificatedAdmin');
        Route::get('congress', 'AdminController@getAdminCongresses');
        Route::group(['prefix' => 'personels'], function () {
            Route::get('list', 'AdminController@getListPersonels');
            Route::post('add', 'AdminController@addPersonnel');
            Route::delete('{admin_id}/delete', 'AdminController@deletePersonnel');
            Route::post('{admin_id}/send-credentials-email', 'AdminController@sendCredentialsViaEmailToOrganizer');
            Route::get('{admin_id}/qr-code', 'AdminController@downloadQrCode');
        });
        Route::group(['prefix' => 'congress'], function () {
            Route::group(['prefix' => '{congressId}'], function () {
                Route::group(['prefix' => 'email'], function () {
                    Route::get('send-confirm-inscription', 'CongressController@sendMailAllParticipants');
                    Route::get('send-mail-all-attestations', 'CongressController@sendMailAllParticipantsAttestation');
                });
                Route::post('edit', 'CongressController@editCongress');
                Route::get('attestation-divers', 'CongressController@getAttestationDiversByCongress');
            });
            Route::post('add', 'CongressController@addCongress');
        });
    });
    Route::group(['prefix' => 'qrcode'], function () {
        Route::post('scan', 'AdminController@scanParticipatorQrCode');
    });
    Route::group(['prefix' => 'participator'], function () {

        Route::group(['prefix' => '{congressId}'], function () {
            Route::get('all', 'AdminController@getAllParticipantsByCongress');
            Route::get('fast-users', 'AdminController@getFastUsersByCongress');
            Route::get('presence', 'AdminController@getAllPresenceByCongress');
        });
        Route::group(['prefix' => '{id_Participator}'], function () {
            Route::post('status/update', 'AdminController@makeUserPresentCongress');
            Route::post('status/update/access', 'AdminController@makeUserPresentAccess');
            Route::post('set-refpayement', 'AdminController@setRefPayment');
            //Route::post('paied-status', 'AdminController@updatePaiedParticipator');
        });
    });


});
//Access API
Route::group(['prefix' => 'access'], function () {
    Route::post('/grant-access-country/{countryId}', 'AccessController@grantAccessByCountry');
    Route::group(['prefix' => 'congress'], function () {
        Route::get('{congress_id}/list', 'AccessController@getAllAccessByCongress');
    });

});

//Pack API
Route::group(['prefix' => 'pack'], function () {
    Route::group(['prefix' => 'congress'], function () {
        Route::get('{congress_id}/list', 'PackController@getAllPackByCongress');
    });

});
//Organisation API
Route::group(['prefix' => 'organization'], function () {
    Route::get('list', 'OrganizationController@getAll');
});

//Privilege API
Route::group(['prefix' => 'privilege'], function () {
    Route::get('list', 'SharedController@getPrivilegesWithBadges');
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

Route::group(['prefix' => 'payment'], function () {
    Route::get('notification', 'PaymentController@notification');
    Route::post('success', 'PaymentController@successPayment');
    Route::get('echec', 'PaymentController@echecPayment');
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
