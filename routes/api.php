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
Route::get('/services', 'SharedController@getAllServices');
Route::get('/etablissements', 'SharedController@getAllEtablissements');
Route::get('/countries', 'SharedController@getAllCountries');
Route::get('/types-attestation', 'SharedController@getAllTypesAttestation');
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
});
/* Files API */

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

//User API
Route::group(['prefix' => 'users'], function () {
    Route::get('', 'UserController@index');

    Route::post('password/reset', 'UserController@forgetPassword');
    Route::post('password/reset/{userId}', 'UserController@resetUserPassword');
    Route::post('/upload-users', 'UserController@uploadUsers');
    Route::post('by-email', 'UserController@getUserByEmail');
    Route::get('congress/{congressId}/all-access', 'UserController@getAllUserAccess')
        ->middleware('assign.guard:users');
    Route::get('confirmInscription/{user_id}', 'UserController@confirmInscription');
    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('', 'UserController@getUserById');

        Route::group(['prefix' => 'congress/{congressId}'], function () {
            Route::delete('delete', 'UserController@delete');
            Route::post('upload-payement', 'UserController@uploadPayement');
            Route::get('sondage', 'UserController@redirectToLinkFormSondage');
            Route::get('', 'UserController@getUserByCongressIdAndUserId');
            Route::get('payment', 'UserController@getUserByCongressIdAndUserIdForPayement');
            Route::get('send-attestation-mail', 'UserController@sendMailAttesation');
            Route::get('send-sondage', 'UserController@sendSondage');
        });

        Route::put('change-paiement', 'UserController@changePaiement');
        Route::get('send-mail/{mail_id}', 'UserController@sendCustomMail');
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
    Route::get('/custom-mail/send-to-all/{mail_id}', 'CongressController@sendCustomMailToAllUsers')->middleware("admin");
    Route::group(['prefix' => '{congress_id}'], function () {
        Route::get('', 'CongressController@getCongressById');
        Route::post('switchRoom','CongressController@switchUsersRoom');
        Route::get('min', 'CongressController@getMinimalCongressById');
        Route::get('/{accessId}/checkUserRights', 'UserController@checkUserRights')->middleware('assign.guard:users');
        Route::get('/checkUserRights', 'UserController@checkUserRights')->middleware('assign.guard:users');
        Route::get('badge', 'CongressController@getCongressByIdBadge');
        Route::get('stats', 'CongressController@getStatsByCongressId');
        Route::get('statsAccess', 'CongressController@getStatsAccessByCongressId');
        Route::get('statsChart', 'CongressController@getStatsChartByCongressId');
        Route::get('config', 'CongressController@getCongressConfigById');
        Route::post('/upload-logo', 'FileController@uploadLogo');
        Route::post('/upload-banner', 'FileController@uploadBanner');
        Route::post('/upload-cv/{userId}', 'FileController@uploadCV');
        Route::get('/logo', 'CongressController@getLogo');
        Route::get('/banner', 'CongressController@getBanner');
        Route::post('badge/affect', 'BadgeController@affectBadgeToCongress');

        Route::get('badge/list', 'BadgeController@getBadgesByCongress');
        Route::post('badge/activate', 'BadgeController@activateBadgeByCongressByPrivilege');


        Route::post('program-link', 'CongressController@setProgramLink');

        Route::get('program_pdf', 'PDFController@generateProgramPDF');

        Route::group(['prefix' => 'attestation'], function () {
            Route::post('affect/{accessId}', 'BadgeController@affectAttestationToCongress')
                ->where('accessId', '[0-9]+');
            Route::post('affect/divers', 'BadgeController@affectAttestationDivers');
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
        Route::get('/detail', 'SubmissionController@getSubmission');
        Route::put('/edit', 'SubmissionController@editSubmssion');
    });
    Route::get('user/all', 'SubmissionController@getSubmissionByUserId');
});

Route::group(['middleware' => ['assign.guard:admins'],'prefix' => 'theme'], function () {
    Route::get('all/{congressId}', 'ThemeController@getAllThemes');
    Route::post('add/{congressId}', 'ThemeController@addExternalTheme');
    Route::get('congress/{congressId}', 'ThemeController@getThemesByCongressId');
});

//User API
Route::group(['prefix' => 'user', "middelware" => "jwt"], function () {

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');

    Route::post('/register', 'UserController@registerUser');

    Route::group(['prefix' => 'congress'], function () {
        Route::get('getMinimalCongress', 'CongressController@getMinimalCongress');
        Route::group(['prefix' => '{congress_id}'], function () {
            Route::get('list-all', 'UserController@getAllUsersByCongress');
            Route::get('list/{privilegeId}', 'UserController@getUsersByCongress');
            Route::get('list-pagination', 'UserController@getUsersByCongressPagination');
            Route::post('add', 'UserController@addUserToCongress');
            Route::post('register', 'UserController@saveUser');
            Route::put('edit/{userId}', 'UserController@editerUserToCongress');
            Route::put('edit-fast-user/{user_id}', 'UserController@editFastUserToCongress');
            Route::get('presence/list', 'UserController@getPresencesByCongress');
            Route::post('status-presence', 'UserController@getUserStatusPresences');
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
        });
    });


});
//Access API
Route::group(['prefix' => 'access'], function () {
    Route::get('{accessId}/user/{userId}/verify-privilege', 'AccessController@verifyPrivilegeByAccess');
});

// Super Admin API
Route::group(['prefix'=> 'admin', 'middleware' => 'marketing'], function () {
    Route::get('all', 'AdminController@getClients');
    Route::get('{admin_id}', "AdminController@getClientById")
        ->where('admin_id', '[0-9]+');
    Route::post('add', 'AdminController@addClient');
    Route::get('mailtype','MailController@getAllMailTypesAdmin');
    Route::get('mail/{mailTypeAdminId}', 'MailController@getMailAdminByMailTypeAdminId');
    Route::get('mailtype/{mailTypeAdminId}', 'MailController@getMailTypeAdminByMailTypeAdminId');
    Route::post('mail/{mailTypeAdminId}', 'MailController@saveMailAdmin');
    Route::put('{admin_id}', "AdminController@editClient");
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
    Route::get('convert-rates','CurrencyController@getConvertCurrency');
});

Route::group(['prefix' => 'payment'], function () {
    Route::post('notification-post', 'PaymentController@notification');
});

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
});
Route::post("switch-qr/{userId}", "UserController@changeQrCode")->middleware('organisateur');

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
