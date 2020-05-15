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

//Functional API
Route::get('/synchroData', 'SharedController@synchroData');
Route::get('deleteOldQrCode', 'SharedController@deleteOldQrCode');
Route::get('/scanAllPresence', 'SharedController@scanAllPresence');

//Shared API
Route::get('/lieu/all', 'SharedController@getAllLieux');
Route::get('/privileges', 'SharedController@getAllPrivileges');
Route::get('/services', 'SharedController@getAllServices');
Route::get('/etablissements', 'SharedController@getAllEtablissements');
Route::get('/countries', 'SharedController@getAllCountries');
Route::get('/types-attestation', 'SharedController@getAllTypesAttestation');
Route::get('/payement-user-recu/{path}', 'SharedController@getRecuPaiement');
Route::get('/feedback-question-types', 'FeedbackController@getFeedbackQuestionTypes');
Route::get('/congress-types', 'SharedController@getAllCongressTypes');


//Front Office Congress
Route::group(['prefix' => 'congress'], function () {
    Route::get('list/pagination', 'CongressController@getCongressPagination');
});
//SMS API

Route::group(['prefix' => 'manage-sms/custom-sms'], function () {
    Route::get('/list', 'CustomSMSController@getListSMS');
    Route::get('/{id}/sms', 'CustomSMSController@getSmsById');
    Route::get('/{id}/users', 'CustomSMSController@filterUsersBySmsStatus');
    Route::get('{id}/send-sms', 'CustomSMSController@sendSmsToUsers');
    Route::post('/configure', 'CustomSMSController@saveCustomSMS');
    Route::delete('/{id}/delete', 'CustomSMSController@deleteSMS');
    Route::delete('/{id}/user/{userId}/delete', 'CustomSMSController@deleteUserSms');
});
/* Files API */
Route::group(['prefix' => 'congress-logo/{path}'], function () {
    Route::get('', 'FileController@getLogoCongress');
    Route::post('delete', 'FileController@deleteLogoCongress');
});

Route::group(['prefix' => 'congress-banner/{path}'], function () {
    Route::get('', 'FileController@getBannerCongress');
    Route::post('delete', 'FileController@deleteBannerCongress');
});
Route::group(['prefix' => 'user-cv/{path}/{userId}'], function () {
    Route::get('', 'FileController@getUserCV');
    Route::post('delete', 'FileController@deleteUserCV');
});

Route::group(['prefix' => 'resource/{path}'], function () {
    Route::get('', 'FileController@getResouce');
    Route::post('delete', 'FileController@deleteResouce');
});

Route::group(['prefix' => 'files'], function () {
    Route::post('/upload-resource', 'FileController@uploadResource');
});

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
            Route::post('status/update', 'AdminController@makeUserPresent');
            Route::post('status/update/access', 'AdminController@makeUserPresentAccess');
        });
    });
});


Route::group(['prefix' => 'auth'], function () {

    Route::group(['middleware' => ['assign.guard:admins']], function () {
        Route::post('login/admin', 'Auth\LoginController@loginAdmin');
        Route::post('forgetPassword', 'Auth\LoginController@forgetPassword');
    });

    Route::group(['middleware' => ['assign.guard:users']], function () {
        Route::post('login/user', 'Auth\LoginController@loginUser');
    });

});

Route::get('/testImpression', 'UserController@testImpression');
//User API
Route::group(['prefix' => 'users'], function () {
    Route::get('', 'UserController@index');
    Route::post('/upload-users', 'UserController@uploadUsers');
    Route::post('by-email', 'UserController@getUserByEmail');
    Route::get('congress/{congressId}/all-access', 'UserController@getAllUserAccess')
        ->middleware('assign.guard:users');
    Route::get('confirmInscription/{user_id}', 'UserController@confirmInscription');
    Route::group(['prefix' => '{user_id}'], function () {

        Route::group(['prefix' => 'congress/{congressId}'], function () {
            Route::delete('delete', 'UserController@delete');
            Route::post('upload-payement', 'UserController@uploadPayement');
            Route::get('sondage', 'UserController@redirectToLinkFormSondage');
            Route::get('validate/{validation_code}', 'UserController@validateUserAccount');
            Route::get('', 'UserController@getUserByCongressIdAndUserId');
            Route::get('send-attestation-mail', 'UserController@sendMailAttesation');
            Route::get('send-sondage', 'UserController@sendSondage');
        });

        Route::put('', 'UserController@update');
        Route::get('sendConfirmationEmail', 'UserController@resendConfirmationMail');
        Route::get('sendingMailWithAttachement', 'UserController@sendingMailWithAttachement');
        Route::put('change-paiement', 'UserController@changePaiement');
        Route::get('send-mail/{mail_id}', 'UserController@sendCustomMail');
        Route::put('set-qr', 'UserController@changeQrCode')->middleware('organisateur');
    });

    //API PER CONGRESS
    Route::group(['prefix' => 'congress/{congress_id}'], function () {
        Route::group(['prefix' => 'privilege'], function () {
            Route::post('', 'UserController@getUserByTypeAndCongressId');
        });
    });


});

//Congress API
Route::group(['prefix' => 'congress', "middelware" => "jwt"], function () {

    Route::get('/all', 'CongressController@getAllCongresses');

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
    Route::get('/custom-mail/send-to-all/{mail_id}', 'CongressController@sendCustomMailToAllUsers')->middleware("admin");
    Route::group(['prefix' => '{congress_id}'], function () {
        Route::get('', 'CongressController@getCongressById');
        Route::get('min', 'CongressController@getMinimalCongressById');
        Route::get('/{accessId}/checkUserRights', 'UserController@checkUserRights')->middleware('assign.guard:users');
        Route::get('badge', 'CongressController@getCongressByIdBadge');
        Route::get('stats', 'CongressController@getStatsByCongressId');
        Route::get('statsAccess', 'CongressController@getStatsAccessByCongressId');
        Route::get('statsChart', 'CongressController@getStatsChartByCongressId');
        Route::get('config', 'CongressController@getCongressConfigById');
        Route::get('/eliminateInscription', 'AdminController@eliminateInscription');
        Route::post('badge/upload', 'BadgeController@uploadBadgeToCongress');
        Route::post('/upload-logo', 'FileController@uploadLogo');
        Route::post('/upload-banner', 'FileController@uploadBanner');
        Route::post('/upload-cv/{userId}', 'FileController@uploadCV');
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
        Route::post('feedback-form', 'FeedbackController@setFeedbackForm')->middleware('admin');
        Route::delete('feedback-form', 'FeedbackController@resetFeedbackForm')->middleware('admin');
        Route::get('feedback-start', 'FeedbackController@getFeedbackStart');
        Route::post('feedback-start', 'FeedbackController@setFeedbackStart')->middleware('admin');
        Route::get('feedback-responses', 'FeedbackController@getFeedbackResponses')->middleware('admin');

    });
});
//Submission API
Route::group(['middleware' => ['assign.guard:admins'], 'prefix' => 'submission'], function () {
    Route::get('congress/{congressId}', 'SubmissionController@getCongressSubmission');
    Route::put('{submissionId}/evaluate/put/', 'SubmissionController@putEvaluationToSubmission');
    Route::get('{submissionId}', 'SubmissionController@getCongressSubmissionDetailById');

});
Route::group(['middleware' => ['assign.guard:users'], 'prefix' => 'submission'], function () {
    Route::post('add', 'SubmissionController@addSubmission');
    Route::group(['prefix' => '{submission_id}'], function () {
        Route::get('', 'SubmissionController@getSubmission');
        Route::put('/edit', 'SubmissionController@editSubmssion');
    });


});
Route::group(['prefix' => 'theme'], function () {
    Route::get('all', 'ThemeController@getAllThemes');
    Route::get('congress/{congressId}', 'ThemeController@getThemesByCongressId');
});
//PackAdmin API
Route::group(['prefix' => 'packadmin'], function () {
    Route::get('list', 'PackAdminController@index');
    Route::get('{pack_id}', 'PackAdminController@getPackById');
    Route::delete('{pack_id}/delete', 'PackAdminController@delete');
    Route::post('add', 'PackAdminController@store');
    Route::put('{pack_id}/update', 'PackAdminController@update');
    Route::get('{pack_id}/modules', 'PackAdminController@getpackmodules');
    Route::get('modules/list', 'PackAdminController@getmodules');
    Route::post('{pack_id}/addmodule/{module_id}', 'PackAdminController@addmoduletoPack');
    Route::get('mails/admin/{mail_id}', 'MailController@getMailAdminById');

    Route::get('clients/all', 'AdminController@getClients');
    Route::get('clients/{adminId}/histories', 'AdminController@getClienthistoriesbyId');
    Route::get('clients/{adminId}/congresses', 'AdminController@getClientcongressesbyId');
    Route::delete('admins/{adminId}/delete', 'AdminController@delete');

    Route::post('admins/{admin_id}/{pack_id}/validate/{history_id}', 'AdminController@ActivatePackForAdmin');
    Route::post('histories/add', 'AdminController@addHistoryToAdmin');

    Route::post('admins/add/{pack_id}', 'AdminController@store');
    Route::get('admins/{adminId}', 'AdminController@getAdminById');
    Route::get('packs/{packId}', 'PackAdminController@getPackById');
    Route::put('admins/{admin_id}/update', 'AdminController@update');
    Route::post('Demo/{admin_id}', 'CongressController@addDemo');
    Route::get('congress/all', 'CongressController@getAll');
    Route::delete('congress/{congress_id}/delete', 'CongressController@delete');
    Route::delete('admins/{admin_id}/{congressId}', 'CongressController@RemoveCongressFromAdmin');
    Route::get('mailtypes/all', 'MailController@getAllMailTypesAdmin');
    Route::put('mails/{mail_id}/update', 'MailController@updateMailAdmin');
    Route::post('mails/add', 'MailController@storeMailAdmin');
});

//User API

Route::group(['prefix' => 'user', "middelware" => "jwt"], function () {

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');


    Route::post('/register', 'UserController@registerUser');

    Route::group(['prefix' => 'congress'], function () {
        Route::get('getMinimalCongress', 'CongressController@getMinimalCongress');
        Route::group(['prefix' => '{congress_id}'], function () {
            Route::get('list-all', 'UserController@getAllUsersByCongress');
            Route::get('list/{privilegeId}', 'UserController@getUsersByCongress');
            Route::get('list-pagination', 'UserController@getUsersByCongressPagination');
            Route::post('list/privilege', 'UserController@getUsersByPrivilegeByCongress');
            Route::post('add', 'UserController@addUserToCongress');
            Route::post('register', 'UserController@saveUser');
            Route::put('edit/{userId}', 'UserController@editerUserToCongress');
            Route::put('edit-fast-user/{user_id}', 'UserController@editFastUserToCongress');
            Route::get('presence/list', 'UserController@getPresencesByCongress');
            Route::post('status-presence', 'UserController@getUserStatusPresences');
            Route::get('mailTest', 'CongressController@sendMailTest');
            Route::post('save-excel', 'UserController@saveUsersFromExcel');

            Route::group(['prefix' => 'access'], function () {
                Route::group(['prefix' => '{access_id}'], function () {
                    Route::get('list', 'UserController@getUsersByAccess');
                    Route::get('presence/list', 'UserController@getPresencesByAccess');
                });
            });
        });
        Route::get('set-attestation-request-status/{user_id}/{done}', 'UserController@setAttestationRequestStatus');

    });

    Route::post('access/presence', 'AdminController@makeUserPresentAccess')
        ->middleware('assign.guard:users');
});
//Admin API
Route::group(['prefix' => 'admin', "middelware" => "admin"], function () {
    Route::group(['prefix' => 'rfid'], function () {
        Route::post('user/{userId}/update', 'AdminController@updateUserRfid');
        Route::post('user/attestations', 'AdminController@getAttestationByUserRfid');

    });
    Route::group(['prefix' => 'room'], function () {
        Route::get('', 'RoomController@getAdminRooms');
        Route::post('', 'RoomController@addAdminRooms');
    });

    Route::put('makePresence/{userId}', 'AdminController@makeUserPresent');
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
                    Route::get('send-mail-all-sondage', 'CongressController@sendMailAllParticipantsSondage');
                });
                Route::post('edit-config', 'CongressController@editConfigCongress');
                Route::get('edit-status/{status}', 'CongressController@editStatus');
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
    Route::get('{accessId}/user/{userId}/verify-privilege', 'AccessController@verifyPrivilegeByAccess');
    Route::post('/grant-access-country/{countryId}', 'AccessController@grantAccessByCountry');
    Route::post("/grant-access-participant/{participantTypeId}", 'AccessController@grantAccessByParticipantType');
    Route::group(['prefix' => 'congress'], function () {
        Route::get('{congress_id}/list', 'AccessController@getAllAccessByCongress');
    });

});

// Super Admin API
Route::group(['middelware' => 'marketing'], function () {
    Route::get('/admin/all', 'AdminController@getClients');
    Route::post('/admin/add', 'AdminController@addClient');
});


//Pack API
Route::group(['prefix' => 'pack'], function () {
    Route::group(['prefix' => 'congress/{congress_id}'], function () {
        Route::get('list', 'PackController@getAllPackByCongress');
        Route::post('add', 'PackController@addPack');
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

//Currency API 
Route::group(['prefix' => 'currency'],function () {
    Route::get('','CurrencyController@getAllCurrencies');
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
Route::get('generateTickets', 'AdminController@generateTickets');
Route::get('updateUsers', 'AdminController@updateUsers');
Route::get('generateUserQrCode', 'AdminController@generateUserQrCode');

Route::group(['prefix' => 'payement'], function () {
    Route::get('/types', 'UserController@getAllPayementTypes');
});

Route::group(["prefix" => "organization", 'middleware' => 'organization'], function () {
    Route::get('/admin/{admin_id}', "OrganizationController@getOrganizationByAdminId");
    Route::get('/admin/{admin_id}/congress/{congressId}', "OrganizationController@getOrganizationByAdminIdAndCongressId");
    Route::get('/{organization_id}', "OrganizationController@getOrganizationById");
    Route::get('/{organizatiolist-paginationn_id}/congress/{congressId}', "OrganizationController@getAllUserByOrganizationId");
    Route::get('/accept/{organization_id}/{user_id}', "OrganizationController@acceptParticipant");
    Route::get('/acceptAll/{organization_id}', "OrganizationController@acceptAllParticipants");
});


Route::group(['prefix' => 'voting', 'middleware' => 'admin'], function () {
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
    Route::get('','AccessController@getAllAccess');
    Route::get('types', 'AccessController@getAccessTypes');
    Route::get('topics', 'AccessController@getAccessTopics');
    Route::post('add/{congress_id}', 'AccessController@addAccess');
    Route::get('get/{access_id}', 'AccessController@getAccessById');
    Route::get('congress/{congress_id}', 'AccessController@getByCongressId');
    Route::get('congress/{access_id}/main', 'AccessController@getMainByCongressId');
    Route::delete('{access_id}', 'AccessController@deleteAccess');
    Route::put('{access_id}', 'AccessController@editAccess');
});

Route::group(["prefix" => "notification"], function () {
    Route::post('/send/{congressId}', 'NotificationController@sendNotificationToCongress');
});
Route::group(["prefix" => "user-app"], function () {
    Route::get('/connect/{qrCode}', 'UserController@userConnect');
    Route::post('/user-connect', 'UserController@userConnectPost')
        ->middleware('assign.guard:users');
    Route::get('/congress', 'CongressController@getAllCongresses');
    Route::get('/congress/{congress_id}', 'CongressController@getCongressById');
    Route::get('/presence/{user_id}', 'UserController@getPresenceStatus');
    Route::post('/presence', 'UserController@getAllPresenceStatus');
    Route::post('/request-attestation/{user_id}', 'UserController@requestAttestations');
    Route::post('/requested-attestation/', 'UserController@requestedAttestations');
    Route::post('/feedback/{user_id}', 'FeedbackController@saveFeedbackResponses');
    //Route::get("quiz", "VotingController@getListPolls");
    // Route::get('/quiz/{congress_id}', 'VotingController@getQuiz');
    Route::post('/quiz', 'VotingController@getQuiz');
    Route::put('/edit-user/{user_id}', 'UserController@mobileEditUser');
    Route::get('/like/{user_id}/{access_id}', 'LikeController@like');
    Route::post('/participant-count', 'CongressController@getParticipantsCounts');
    Route::post('/profile-pic/{user_id}', 'UserController@uploadProfilePic');
    Route::get('/profile-pic/{user_id}', 'UserController@getProfilePic');
    Route::post('/send-firebase-key/{congress_id}', 'NotificationController@sendFirebaseKey');
});
