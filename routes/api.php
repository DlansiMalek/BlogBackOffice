<?php

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
Route::get('/lieu/all', 'SharedController@getAllLieux');
Route::get('/privileges', 'SharedController@getAllPrivileges');
Route::get('/countries', 'SharedController@getAllCountries');
Route::get('/types-attestation', 'SharedController@getAllTypesAttestation');
Route::get('/congress-logo/{path}', 'SharedController@getLogoCongress');
Route::get('/congress-banner/{path}', 'SharedController@getBannerCongress');
Route::get('/payement-user-recu/{path}', 'SharedController@getRecuPaiement');
Route::get('/feedback-question-types', 'FeedbackController@getFeedbackQuestionTypes');

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
            Route::post('statgetListPersonelsByAdminus/update', 'AdminController@makeUserPresentCongress');
            Route::post('status/update/access', 'AdminController@makeUserPresentAccess');
        });
    });
});


Route::post('auth/login/admin', 'Auth\LoginController@loginAdmin');
Route::post('auth/forgetPassword', 'Auth\LoginController@forgetPassword');

Route::get('/testImpression', 'UserController@testImpression');
//User API
Route::group(['prefix' => 'users'], function () {
    Route::get('', 'UserController@index');

    Route::post('by-email', 'UserController@getUserByEmail');
    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('', 'UserController@getUserById');
        Route::put('', 'UserController@update');
        Route::delete('congress/{congressId}/delete', 'UserController@delete');
        Route::post('upload-payement', 'UserController@uploadPayement');
        Route::get('validate/{validation_code}', 'UserController@validateUserAccount');
        Route::get('sendConfirmationEmail', 'UserController@resendConfirmationMail');
        Route::get('sendingMailWithAttachement', 'UserController@sendingMailWithAttachement');
        Route::put('change-paiement', 'UserController@changePaiement');
        Route::get('send-attestation-mail', 'UserController@sendMailAttesation');
        Route::get('send-mail/{mail_id}', 'UserController@sendCustomMail');
        Route::put('set-qr', 'UserController@changeQrCode')->middleware('organisateur');
    });

    //API PER CONGRESS
    Route::group(['prefix' => 'congress/{congress_id}'], function () {
        Route::group(['prefix' => 'privilege'], function () {
            Route::get('{privilege_id}', 'UserController@getUserByTypeAndCongressId');
        });
    });


});

//Congress API
Route::group(['prefix' => 'congress', "middelware" => "jwt"], function () {


    Route::group(['prefix' => 'mail'], function () {
        Route::get('{mailId}', 'MailController@getById');
        Route::get('types/{typeId}', 'MailController@getMailTypeById');
        Route::get('types/{typeId}/congress/{congressId}', 'MailController@getByMailTypeAndCongress');
    });
    Route::group(["prefix" => 'form'], function () {
        Route::get('input-types', 'RegistrationFormController@getInputTypes');
        Route::get('{congress_id}', 'RegistrationFormController@getForm');
        Route::post('{congress_id}', 'RegistrationFormController@setForm')->middleware('admin');
    });
    Route::post('upload-mail-image', 'CongressController@uploadMailImage');
    Route::get('file/{file_path}', 'SharedController@getFile');
    Route::get('/custom-mail/send-to-all/{mail_id}', 'CongressController@sendCustomMailToAllUsers')->middleware("super-admin");
    Route::group(['prefix' => '{congress_id}'], function () {
        Route::get('', 'CongressController@getCongressById');
        Route::get('config', 'CongressController@getCongressConfigById');
        Route::get('/eliminateInscription', 'AdminController@eliminateInscription');
        Route::get('/sendMailAllParticipants', 'AdminController@sendMailAllParticipants');
        Route::get('badges', 'CongressController@getBadgesByCongress');
        Route::post('badge/upload', 'BadgeController@uploadBadgeToCongress');

        Route::post('/upload-logo', 'CongressController@uploadLogo');
        Route::post('/upload-banner', 'CongressController@uploadBanner');
        Route::get('/logo', 'CongressController@getLogo');
        Route::get('/banner', 'CongressController@getBanner');
        Route::post('badge/affect', 'BadgeController@affectBadgeToCongress');
        Route::get('badge/apercu', 'BadgeController@apercuBadge');
        Route::post('program-link', 'CongressController@setProgramLink');

        Route::get('program_pdf', 'PDFController@generateProgramPDF');

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
        Route::get('mail/types', 'MailController@getAllMailTypes');
        Route::post('mail/type/{mailTypeId}', 'MailController@saveMail');
        Route::post('organization', 'OrganizationController@addOrganization');
        Route::get('organization', 'OrganizationController@getCongressOrganizations');
        Route::get('feedback-form', 'FeedbackController@getFeedbackForm');
        Route::post('feedback-form', 'FeedbackController@setFeedbackForm')->middleware('super-admin');
        Route::delete('feedback-form', 'FeedbackController@resetFeedbackForm')->middleware('super-admin');
        Route::get('feedback-start', 'FeedbackController@getFeedbackStart');
        Route::post('feedback-start', 'FeedbackController@setFeedbackStart')->middleware('super-admin');
        Route::get('feedback-responses', 'FeedbackController@getFeedbackResponses')->middleware('super-admin');

    });
});

//PackAdmin API
/*@todo finish pack admin api */
Route::group(['prefix' => 'packadmin'], function () {
    Route::get('list', 'PackAdminController@index');
    Route::get('{pack_id}', 'PackAdminController@getPackById');
    Route::delete('{pack_id}/delete', 'PackAdminController@delete');
    Route::post('add', 'PackAdminController@store');
    Route::put('{pack_id}/update', 'PackAdminController@update');
    Route::get('{pack_id}/modules', 'PackAdminController@getpackmodules');
    Route::get('modules/list', 'PackAdminController@getmodules');
    Route::post('{pack_id}/addmodule/{module_id}', 'PackAdminController@addmoduletoPack');

    Route::get('clients/all', 'AdminController@getClients');
    Route::delete('admins/{adminId}/delete', 'AdminController@delete');
    Route::post('admins/add/{pack_id}', 'AdminController@store');
    Route::put('admins/{admin_id}/update', 'AdminController@update');
    Route::post('Demo', 'CongressController@addDemo');
    Route::get('congress/all', 'CongressController@getAll');
    Route::delete('congress/{congress_id}/delete', 'CongressController@delete');

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
            Route::post('register', 'UserController@saveUser');
            Route::put('edit', 'UserController@editerUserToCongress');
            Route::post('add-fast-user', 'UserController@addingFastUserToCongress');
            Route::put('edit-fast-user/{user_id}', 'UserController@editFastUserToCongress');
            Route::get('presence/list', 'UserController@getPresencesByCongress');
            Route::post('status-presence', 'UserController@getUserStatusPresences');
            Route::get('mailTest', 'CongressController@sendMailTest');
            Route::post('save-excel', 'UserController@saveUsersFromExcel');
        });
        Route::get('set-attestation-request-status/{user_id}/{done}', 'UserController@setAttestationRequestStatus');

    });
    Route::group(['prefix' => 'access'], function () {
        Route::group(['prefix' => '{access_id}'], function () {
            Route::get('list', 'UserController@getUsersByAccess');
            Route::get('presence/list', 'UserController@getPresencesByAccess');
        });


    });
});
//Admin API
Route::group(['prefix' => 'admin', "middelware" => "admin"], function () {
    Route::group(['prefix' => 'rfid'], function () {
        Route::post('user/{userId}/update', 'AdminController@updateUserRfid');
        Route::post('user/attestations', 'AdminController@getAttestationByUserRfid');

    });
    Route::group(['prefix' => 'me'], function () {
        Route::get('', 'AdminController@getAuhenticatedAdmin');
        Route::get('congress', 'AdminController@getAdminCongresses');
        Route::group(['prefix' => 'personels'], function () {
            Route::get('list/{congress_id}', 'AdminController@getListPersonels');
            Route::put('{congress_id}/edit/{admin_id}', 'AdminController@editPersonels');
            Route::get('{congress_id}/byId/{admin_id}', 'AdminController@getPersonelByIdAndCongressId');
            Route::post('{congress_id}/add', 'AdminController@addPersonnel');
            Route::delete('{congress_id}/delete/{admin_id}', 'AdminController@deletePersonnel');
            Route::post('{admin_id}/send-credentials-email', 'AdminController@sendCredentialsViaEmailToOrganizer');
            Route::get('{admin_id}/qr-code', 'AdminController@downloadQrCode');
        });
        Route::group(['prefix' => 'congress'], function () {
            Route::group(['prefix' => '{congressId}'], function () {
                Route::group(['prefix' => 'email'], function () {
                    Route::get('send-confirm-inscription', 'CongressController@sendMailAllParticipants');
                    Route::get('send-mail-all-attestations', 'CongressController@sendMailAllParticipantsAttestation');
                });
                Route::post('edit-config', 'CongressController@editConfigCongress');
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
    Route::post("/grant-access-participant/{participantTypeId}", 'AccessController@grantAccessByParticipantType');
    Route::group(['prefix' => 'congress'], function () {
        Route::get('{congress_id}/list', 'AccessController@getAllAccessByCongress');
    });

});

// Super Admin API
Route::group(['middelware' => 'marketing'], function () {

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

Route::group(['prefix' => 'payement'], function () {
    Route::get('/types', 'UserController@getAllPayementTypes');
});

Route::group(["prefix" => "organization", 'middleware' => 'organization'], function () {
    Route::get('/admin/{admin_id}', "OrganizationController@getOrganizationByAdminId");
    Route::get('/{organization_id}', "OrganizationController@getOrganizationById");
    Route::get('/accept/{organization_id}/{user_id}', "OrganizationController@acceptParticipant");
    Route::get('/acceptAll/{organization_id}', "OrganizationController@acceptAllParticipants");
});


Route::group(['prefix' => 'voting', 'middleware' => 'super-admin'], function () {
    Route::put('{congress_id}/token', 'VotingController@setToken');
    Route::get('{congress_id}/token', 'VotingController@getToken');
    Route::get('{congress_id}', 'VotingController@getAssociation');
    Route::put('{congress_id}', 'VotingController@setAssociation');
    Route::delete('{congress_id}', 'VotingController@resetAssociation');
});
Route::group(["prefix" => "voting-users"], function () {
    Route::get("polls", "VotingController@getListPolls");
    Route::post("polls", "VotingController@getMultipleListPolls");
    Route::post("send-scores", "VotingController@sendScores");
});
Route::post("switch-qr/{userId}", "UserController@changeQrCode")->middleware('organisateur');


Route::get('encrypt/{password}', 'SharedController@encrypt');

Route::group(['prefix' => 'resource'], function () {
    Route::post('', 'ResourcesController@uploadResource')->middleware('admin');
});

Route::group(['prefix' => 'access'], function () {
    Route::get('types', 'AccessController@getAccessTypes');
    Route::get('topics', 'AccessController@getAccessTopics');
    Route::post('add/{congress_id}', 'AccessController@addAccess');
    Route::get('get/{access_id}', 'AccessController@getAccessById');
    Route::get('congress/{access_id}', 'AccessController@getByCongressId');
    Route::get('congress/{access_id}/main', 'AccessController@getMainByCongressId');
    Route::delete('{access_id}', 'AccessController@deleteAccess');
    Route::put('{access_id}', 'AccessController@editAccess');
});

Route::group(["prefix" => "user-app"], function () {
    Route::get('/connect/{qrCode}', 'UserController@userConnect');
    Route::get('/congress', 'CongressController@getAllCongresses');
    Route::get('/congress/{congress_id}', 'CongressController@getCongressById');
    Route::get('/congress', 'CongressController@getAllCongresses');
    Route::get('/presence/{user_id}', 'UserController@getPresenceStatus');
    Route::post('/presence', 'UserController@getAllPresenceStatus');
    Route::post('/request-attestation/{user_id}', 'UserController@requestAttestations');
    Route::post('/requested-attestation/', 'UserController@requestedAttestations');
    Route::post('/feedback/{user_id}', 'FeedbackController@saveFeedbackResponses');
//    Route::get("quiz", "VotingController@getListPolls");
    // Route::get('/quiz/{congress_id}', 'VotingController@getQuiz');
    Route::post('/quiz', 'VotingController@getQuiz');
    Route::put('/edit-user/{user_id}', 'UserController@mobileEditUser');
    Route::get('/like/{user_id}/{access_id}', 'LikeController@like');
    Route::post('/participant-count', 'CongressController@getParticipantsCounts');
    Route::post('/profile-pic/{user_id}', 'UserController@uploadProfilePic');
    Route::get('/profile-pic/{user_id}', 'UserController@getProfilePic');
});
