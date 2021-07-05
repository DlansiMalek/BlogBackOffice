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

Route::group(['middleware' => ['web']], function () {
    Route::get('login/google', 'Auth\LoginController@redirectToGoogleProvider');
    Route::get('login/google/callback', 'Auth\LoginController@handleGoogleProviderCallback');
    Route::get('login/facebook', 'Auth\LoginController@redirectToFacebookProvider');
    Route::get('login/facebook/callback', 'Auth\LoginController@handleFacebookProviderCallback');
});

Route::get('/congress/{congressId}/migrateImgDataUsers', 'UserController@migrateUsersData');

Route::get('updateTokens/{congressId}', 'AccessController@updateTokensJitsi');
//Shared API
Route::get('/lieu/all', 'SharedController@getAllLieux');
Route::get('/privileges', 'SharedController@getAllPrivileges');
Route::get('/services', 'SharedController@getAllServices');
Route::get('/etablissements', 'SharedController@getAllEtablissements');
Route::get('/communication_type', 'SharedController@getAllCommunicationTypes');
Route::get('/countries', 'SharedController@getAllCountries');
Route::get('/types-attestation', 'SharedController@getAllTypesAttestation');
Route::get('/feedback-question-types', 'FeedbackController@getFeedbackQuestionTypes');
Route::get('/congress-types', 'SharedController@getAllCongressTypes');
Route::get('/payement-user-recu/{path}', 'SharedController@getRecuPaiement');
Route::get('/submissions/congress/{congressId}', 'SubmissionController@getAllSubmissionsByCongress');
Route::get('/confirm/{congress_id}/{user_id}/{present}', 'CongressController@confirmPresence');
Route::get('/action', 'SharedController@getAllActions');

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
Route::group(['prefix' => 'files'], function () {
    Route::post('/upload-resource', 'FileController@uploadResource');
    Route::post('/delete-resource/{path}', 'FileController@deleteResouce');
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

// Tracking API
Route::group(['prefix' => 'tracking', 'middleware' => ['assign.guard:admins']], function () {
    Route::group(['prefix' => 'congress/{congressId}'], function () {
        Route::post('migrate-users', 'TrackingController@migrateUsers');
        Route::post('migrate-tracking', 'TrackingController@migrateTracking');
    });
});

//User API
Route::group(['prefix' => 'users'], function () {
    Route::get('', 'UserController@index');
    Route::post('changeMultipleStatus/{congressId}', 'UserController@changeMultipleUsersStatus');
    Route::post('password/reset', 'UserController@forgetPassword');
    Route::post('password/reset/{userId}', 'UserController@resetUserPassword');
    Route::post('/upload-users', 'UserController@uploadUsers');
    Route::post('by-email', 'UserController@getUserByEmail');
    Route::get('congress/{congressId}/all-access', 'UserController@getAllUserAccess')
        ->middleware('assign.guard:users');

    Route::post('tracking', 'UserController@trackingUser')
        ->middleware('assign.guard:users');
    Route::get('confirmInscription/{user_id}', 'UserController@confirmInscription');
    Route::group(['prefix' => '{user_id}'], function () {
        Route::delete('deleteUserOutOfCongress', 'UserController@delete');
        Route::get('', 'UserController@getUserById');
        Route::group(['prefix' => 'congress/{congressId}'], function () {
            Route::post('changeStatus', 'UserController@changeUserStatus');
            Route::delete('delete', 'UserController@delete');
            Route::post('update-payment', 'UserController@updateUserPayment');
            Route::get('sondage', 'UserController@redirectToLinkFormSondage');
            Route::get('validate/{validation_code}', 'UserController@validateUserAccount');
            Route::get('', 'UserController@getUserByCongressIdAndUserId');
            Route::get('payment', 'UserController@getUserByCongressIdAndUserIdForPayement');
            Route::get('send-attestation-mail', 'UserController@sendMailAttesation');
            Route::get('send-sondage', 'UserController@sendSondage');
        });
        Route::put('change-paiement', 'UserController@changePaiement');
        Route::get('send-mail/{mail_id}/{congress_id}', 'UserController@sendCustomMail');
    });

    //API PER CONGRESS
    Route::group(['prefix' => 'congress/{congress_id}'], function () {
        Route::group(['prefix' => 'privilege'], function () {
            Route::post('', 'UserController@getUserByTypeAndCongressId');
        });
    });
});


//Congress API
Route::group(['prefix' => 'congress', "middleware" => ['assign.guard:admins']], function () {

    Route::get('/all', 'CongressController@getAllCongresses');
    Route::get('minCongressData', 'CongressController@getMinCongressData');
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
    Route::post('/custom-mail/send-to-all/{mail_id}', 'CongressController@sendCustomMailToAllUsers');
    Route::group(['prefix' => '{congress_id}'], function () {
        Route::get('', 'CongressController@getCongressById');
        Route::get('/details', 'CongressController@getCongressDetailsById');
        Route::post('switchRoom', 'CongressController@switchUsersRoom');
        Route::post('addItemsEvaluation', 'CongressController@addItemsEvaluation')->middleware('assign.guard:admins');
        Route::get('getItemsEvaluation', 'CongressController@getItemsEvaluation')->middleware('assign.guard:admins');
        Route::post('addItemsNote/{evaluation_inscription_id}', 'CongressController@addItemsNote')->middleware('assign.guard:admins');
        Route::get('min', 'CongressController@getMinimalCongressById');
        Route::get('/{accessId}/checkUserRights', 'UserController@checkUserRights')->middleware('assign.guard:users');
        Route::get('/checkUserRights', 'UserController@checkUserRights')->middleware('assign.guard:users');
        Route::get('badge', 'CongressController@getCongressByIdBadge');
        Route::get('stats', 'CongressController@getStatsByCongressId');
        Route::get('statsAccess', 'CongressController@getStatsAccessByCongressId');
        Route::get('statsStand', 'CongressController@getStatsStandByCongressId');
        Route::get('statsChart', 'CongressController@getStatsChartByCongressId');
        Route::get('config', 'CongressController@getCongressConfigById');
        Route::get('/logo', 'CongressController@getLogo');
        Route::get('/banner', 'CongressController@getBanner');
        Route::post('badge/affect', 'BadgeController@affectBadgeToCongress');
        Route::delete('/delete-badge/{badgeId}', 'BadgeController@deleteBadge');
        Route::get('/tracking', 'CongressController@getListTrackingByCongress');
        Route::get('badge/list', 'BadgeController@getBadgesByCongress');
        Route::post('badge/activate', 'BadgeController@activateBadgeByCongressByPrivilege');
        Route::get('attestation-submission/list', 'SubmissionController@getAttestationSubmissionByCongress')->middleware("admin");
        Route::post('attestation-submission/activate', 'SubmissionController@activateAttestationByCongressByType')->middleware("admin");
        Route::post('attestation-submission/delete', 'SubmissionController@deleteAttestationByCongress')->middleware("admin");
        Route::post('attestation-submission/affect', 'SubmissionController@affectAttestationToCongress')->middleware("admin");
        Route::get('attestation-submission/enabled', 'SubmissionController@getAttestationSubmissionEnabled')->middleware("admin");
        Route::get('/{standId}/checkStandRights', 'UserController@checkStandRights')->middleware('assign.guard:users');
        Route::get('/{standId}/checkSupportRights/{organizerId}', 'UserController@checkStandRights')->middleware('assign.guard:users');
        Route::get('getOrganizers', 'UserController@getOrganizers')->middleware('assign.guard:users');

        Route::post('program-link', 'CongressController@setProgramLink');
        Route::post('/abstractBook', 'CongressController@affectAbstractBookPathToCongress');
        Route::post('/congress-logo', 'CongressController@affectLogoToCongress');
        Route::get('access/change-status', 'AccessController@editAccessStatus');


        Route::get('program_pdf', 'PDFController@generateProgramPDF');
        Route::group(['prefix' => 'stand'], function () {
            Route::get('', 'StandController@getStands');
            Route::get('/getStandById/{stand_id}', 'StandController@getStandById');
            Route::post('/add', 'StandController@addStand');
            Route::get('docs', 'StandController@getDocsByCongress');
			Route::get('/standsPagination/{offset}', 'StandController@getStandsByCongressPagination');
            Route::put('/change-status', 'StandController@modiyStatusStand');
            Route::get('/get-status', 'StandController@getStatusStand');
            Route::delete('deleteStand/{stand_id}', 'standController@deleteStand');
            Route::delete('/deletestandproduct/{stand_product_id}', 'StandProductController@deleteStandproduct');
            Route::get('{stand_id}/products', 'StandProductController@getStandproducts');
            Route::post('/addproduct', 'StandProductController@addStandProduct');
            Route::put('/edit/{standId}/{standproduct_id}', 'StandProductController@editStandProduct');
            Route::group(['prefix' => '{stand_id}/FAQ'], function () {
                Route::get('', 'FAQController@getStandFAQs');
                Route::put('', 'FAQController@addFAQ');
              });
        });


        Route::group(['prefix' => 'attestation'], function () {
            Route::post('affect/{accessId}', 'BadgeController@affectAttestationToCongress')
                ->where('accessId', '[0-9]+');
            Route::post('affect/divers', 'BadgeController@affectAttestationDivers');
        });

        Route::get('mail/types', 'MailController@getAllMailTypes');
        Route::post('mail/type/{mailTypeId}', 'MailController@saveMail');
        Route::delete('mail/delete/{mail_id}', 'MailController@deleteMail');
        Route::post('organization', 'OrganizationController@addOrganization');
        Route::get('organization', 'OrganizationController@getCongressOrganizations');
        Route::get('sponsors', 'OrganizationController@getSponsorsByCongressId');
        Route::get('organismes', 'OrganizationController@getOrganizmeByCongress');
        Route::delete('delete-organization/{organization_id}', 'OrganizationController@deleteOrganization');


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
    Route::get('types', 'SubmissionController@getSubmissionType');
    Route::get('congress/{congressId}', 'SubmissionController@getCongressSubmission');
    Route::get('{submissionId}/send-mail-attestation/{congressId}', 'SubmissionController@sendMailAttestationById');
    Route::get('{congressId}/status/{status}', 'SubmissionController@getSubmissionByStatus');
    Route::get('{submissionId}/make_eligible/{congressId}', 'SubmissionController@makeSubmissionEligible');
    Route::put('{submissionId}/evaluate/put/', 'SubmissionController@putEvaluationToSubmission');
    Route::post('congress/{congressId}/changeSubmissionsStatus', 'SubmissionController@changeMultipleSubmissionsStatus');
    Route::put('{submissionId}/evaluate/type/put/', 'SubmissionController@putEvaluationToSubmission');
    Route::get('{submissionId}', 'SubmissionController@getCongressSubmissionDetailById');
    Route::put('{submissionId}/finalDecisionOnSubmission', 'SubmissionController@finalDecisionOnSubmission');
    Route::delete('{submissionId}', 'SubmissionController@deleteSubmission');
    Route::put('{submissionId}/{congressId}/change-status', 'SubmissionController@changeSubmissionStatus');
    Route::post('{congressId}/upload-submissions', 'SubmissionController@uploadSubmissions');
});

Route::group(['middleware' => ['assign.guard:users'], 'prefix' => 'submission'], function () {
    Route::post('add', 'SubmissionController@addSubmission');
    Route::group(['prefix' => '{submission_id}'], function () {
        Route::get('/detail', 'SubmissionController@getSubmission');
        Route::put('/edit', 'SubmissionController@editSubmssion');
    });
    Route::get('user/all/pagination', 'SubmissionController@getSubmissionByUserId');
});

Route::group(['middleware' => ['assign.guard:admins'], 'prefix' => 'theme'], function () {
    Route::get('all/{congressId}', 'ThemeController@getAllThemes');
    Route::post('add/{congressId}', 'ThemeController@addExternalTheme');
    Route::get('congress/{congressId}', 'ThemeController@getThemesByCongressId');
});

//User API
Route::group(['prefix' => 'user', "middleware" => ['assign.guard:admins']], function () {

    Route::get('{user_id}/qr-code', 'UserController@getQrCodeUser');

    Route::get('me', 'UserController@getLoggedUser')
        ->middleware('assign.guard:users');

    Route::group(['prefix' => 'contact', 'middleware' => 'assign.guard:users'], function () {

        Route::post('', 'UserController@addContact');
        Route::delete('{userId}', 'UserController@deleteContact');
        Route::get('', 'UserController@listContacts');
    });

    Route::put('edit/profile', 'UserController@editUserProfile')->middleware('assign.guard:users');

    Route::post('/register', 'UserController@registerUser');

    Route::group(['prefix' => 'congress'], function () {
        Route::get('getMinimalCongress', 'CongressController@getMinimalCongress');
        Route::group(['prefix' => '{congress_id}'], function () {
            Route::post('{user_id}/changeScore', 'UserController@affectScoreToUser');
            Route::get('list-all', 'UserController@getAllUsersByCongress');
            Route::get('list/{privilegeId}', 'UserController@getUsersByCongress');
            Route::get('list-pagination', 'UserController@getUsersByCongressPagination');
            Route::post('add', 'UserController@addUserToCongress');
            Route::post('register', 'UserController@saveUser');
            Route::post('registerV2', 'UserController@saveUserInscription')->middleware('assign.guard:users');
            Route::put('edit/{userId}', 'UserController@editerUserToCongress');
            Route::put('edit-fast-user/{user_id}', 'UserController@editFastUserToCongress');
            Route::get('presence/list', 'UserController@getPresencesByCongress');
            Route::post('status-presence', 'UserController@getUserStatusPresences');
            Route::post('save-excel', 'UserController@saveUsersFromExcel');
            Route::post('set-current-participant', 'CongressController@setCurrentParticipants');
            Route::group(['prefix' => 'access'], function () {
                Route::group(['prefix' => '{access_id}'], function () {
                    Route::get('list', 'UserController@getUsersByAccess');
                    Route::get('presence/list', 'UserController@getPresencesByAccess');
                });
            });
            Route::group(['prefix' => 'white-list'], function () {
                Route::get('', 'UserController@getWhiteList');
                Route::post('', 'UserController@addWhiteList');
                Route::delete('{white_list_id}', 'UserController@deleteWhiteList');
            });
            Route::get('stands', 'StandController@getStandsByCongress');
        });
        Route::get('set-attestation-request-status/{user_id}/{done}', 'UserController@setAttestationRequestStatus');
    });

    Route::post('access/presence', 'AdminController@makeUserPresentAccess')
        ->middleware('assign.guard:users');
    Route::post('access/presence/{userId}', 'AdminController@makeUserPresentAccess');
    Route::get('me/events', 'CongressController@getUserCongress')
        ->middleware('assign.guard:users');
    Route::post('/update-path-cv/{userId}', 'UserController@updateUserPathCV');
    Route::get('/delete-user-cv/{user_id}', 'UserController@deleteUserCV');
});
//Admin API
Route::group(['prefix' => 'admin', "middleware" => ["assign.guard:admins"]], function () {
    Route::get('migrate-users-data', 'UserController@migrateUsersData');
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
        Route::get('/congress/{congress_id}', 'AdminController@getAdminWithCurrentCongressFirst');
        Route::get('congress', 'AdminController@getAdminCongresses');
        Route::group(['prefix' => 'personels'], function () {
            Route::get('list/{congress_id}', 'AdminController@getListPersonels');
            Route::get('list/{congress_id}/organismadmins', 'AdminController@getListOrganismAdmins');
            Route::get('list/{congress_id}/privilege/{privilege_id}', 'AdminController@getAdminsByPrivilege');
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
                    Route::get('send-mails-all-attestations-submissions', 'SubmissionController@sendMailAttestationAllSubmission');
                    Route::get('send-mail-all-sondage', 'CongressController@sendMailAllParticipantsSondage');
                });
                Route::post('edit-config', 'CongressController@editConfigCongress');
                Route::get('edit-status/{status}', 'CongressController@editStatus');
                Route::post('edit', 'CongressController@editCongress');
                Route::get('attestation-divers', 'CongressController@getAttestationDiversByCongress');

                Route::group(['prefix' => 'landing-page'], function () {
                    Route::post('edit-config', 'CongressController@editConfigLandingPage');
                    Route::get('get-config', 'CongressController@getConfigLandingPage');
                    Route::post('add-speaker', 'CongressController@addLandingPageSpeaker');
                    Route::get('get-speakers', 'CongressController@getLandingPageSpeakers');
                    Route::get('syncronize', 'CongressController@syncronizeLandingPage');
                });
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

    Route::group(['prefix' => 'menus'], function () {
        Route::post('add/{congress_id}/{privilege_id}', 'OffreController@addPrivilegeMenuChildren');
        Route::get('{congress_id}/{privilege_id}', 'OffreController@getMenusByPrivilegeByCongress');
        Route::post('edit/{congress_id}/{privilege_id}', 'OffreController@editPrivilegeMenuChildren');
    });
});

Route::group(['prefix' => 'landing-page-speakers', "middleware" => ["assign.guard:admins"]], function () {
    Route::post('edit/{lp_speaker_id}', 'CongressController@editLandingPageSpeaker');
    Route::delete('delete/{lp_speaker_id}', 'CongressController@deleteLandingPageSpeaker');
});

//Access API
Route::group(['prefix' => 'access'], function () {
    Route::get('{accessId}/user/{userId}/verify-privilege', 'AccessController@verifyPrivilegeByAccess');
});

// Super Admin API
Route::group(['prefix' => 'admin', 'middleware' => 'marketing'], function () {
    Route::get('all', 'AdminController@getClients');
    Route::get('{admin_id}', "AdminController@getClientById")
        ->where('admin_id', '[0-9]+');
    Route::post('add', 'AdminController@addClient');
    Route::get('mailtype', 'MailController@getAllMailTypesAdmin');
    Route::get('mail/{mailTypeAdminId}', 'MailController@getMailAdminByMailTypeAdminId');
    Route::get('mailtype/{mailTypeAdminId}', 'MailController@getMailTypeAdminByMailTypeAdminId');
    Route::post('mail/{mailTypeAdminId}', 'MailController@saveMailAdmin');
    Route::put('{admin_id}', "AdminController@editClient");
    Route::put('{admin_id}/offre/{offreId}', "AdminController@editClientPayment");
});

Route::group(['prefix' => 'offre', 'middleware' => 'marketing'], function () {
    Route::get('list', 'OffreController@getAllOffres');
    Route::post('add', 'OffreController@addOffre');
    Route::get('get/{offre_id}', 'OffreController@getOffreById');
    Route::put('edit/{offre_id}', 'OffreController@editOffre');
});

Route::group(['prefix' => 'menu', 'middleware' => 'marketing'], function () {
    Route::get('list', 'OffreController@getAllMenu');
    Route::group(['prefix' => 'menu-children'], function () {
        Route::get('list', 'OffreController@getAllMenuChildren');
    });
});

//Pack API
Route::group(['prefix' => 'pack'], function () {
    Route::group(['prefix' => 'congress/{congress_id}'], function () {
        Route::get('list', 'PackController@getAllPackByCongress');
        Route::post('add', 'PackController@addPack');
        Route::get('get/{packId}', 'PackController@getPackById');
        Route::put('{pack_id}', 'PackController@editPack');
    });
    Route::delete('delete/{packId}', 'PackController@deletePack');
});
//Organisation API
Route::group(['prefix' => 'organization', "middleware" => ["assign.guard:admins"]], function () {
    Route::get('list', 'OrganizationController@getAll');
});

//Privilege API
Route::group(['prefix' => 'privilege', "middleware" => ["assign.guard:admins"]], function () {
    Route::get('{congress_id}/list-base', 'PrivilegeController@getPrivilegesDeBase');
    Route::get('{congress_id}/list', 'PrivilegeController@getPrivilegesByCongress');
    Route::get('{congress_id}/list-correspondence', 'PrivilegeController@getAllPrivilegesCorrespondents');
    Route::post('addPrivilege', 'PrivilegeController@addPrivilege');
    Route::get('getPrivilegeById/{id_privilege}', 'PrivilegeController@getPrivilegeById');
    Route::delete('{congress_id}/deletePrivilege/{id_privilege}', 'PrivilegeController@deletePrivilege');
    Route::get('{congress_id}/hidePrivilege/{id_privilege}', 'PrivilegeController@hidePrivilege');
    Route::get('{congress_id}/activatePrivilege/{id_privilege}', 'PrivilegeController@activatePrivilege');
});


//Currency API 
Route::group(['prefix' => 'currency'], function () {
    Route::get('', 'CurrencyController@getAllCurrencies');
    Route::get('convert-rates', 'CurrencyController@getConvertCurrency');
});

Route::group(['prefix' => 'payment'], function () {
    Route::get('notification', 'PaymentController@notification');
    Route::post('success', 'PaymentController@successPayment');
    Route::get('echec', 'PaymentController@echecPayment');
    Route::post('notification-post', 'PaymentController@notification');
});

Route::group(['prefix' => 'payement'], function () {
    Route::get('/types', 'UserController@getAllPayementTypes');
});

Route::group(["prefix" => "organization", 'middleware' => 'organization'], function () {
    Route::get('/admin/{admin_id}', "OrganizationController@getOrganizationByAdminId");
    Route::get('/admin/{admin_id}/congress/{congressId}', "OrganizationController@getOrganizationByAdminIdAndCongressId");
    Route::get('/{organization_id}/{congress_id}', "OrganizationController@getOrganizationById");
    Route::get('/{organizatiolist-paginationn_id}/congress/{congressId}', "OrganizationController@getAllUserByOrganizationId");
    Route::get('/accept/{organization_id}/{user_id}', "OrganizationController@acceptParticipant");
    Route::get('/acceptAll/{organization_id}', "OrganizationController@acceptAllParticipants");
    Route::post('{congressId}', 'OrganizationController@saveOrganizationsFromExcel');

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
    Route::post('vote', 'VotingController@voteUser')
        ->middleware('assign.guard:users');
});
Route::post("switch-qr/{userId}", "UserController@changeQrCode")->middleware('organisateur');
Route::get('encrypt/{password}', 'SharedController@encrypt');

Route::group(['prefix' => 'access'], function () {
    Route::get('', 'AccessController@getAllAccess');
    Route::get('types', 'AccessController@getAccessTypes');
    Route::get('topics', 'AccessController@getAccessTopics');
    Route::post('add/{congress_id}', 'AccessController@addAccess');
    Route::get('get/{access_id}', 'AccessController@getAccessById');
    Route::get('congress/{congress_id}', 'AccessController@getByCongressId');
    Route::get('congress/{access_id}/main', 'AccessController@getMainByCongressId');
    Route::delete('{access_id}', 'AccessController@deleteAccess');
    Route::put('{access_id}', 'AccessController@editAccess');
    Route::get('congress/{congress_id}/scores', 'AccessController@getScoresByCongressId');
    Route::delete('reset-score/{access_id}', 'AccessController@resetScore');
    Route::post('{congress_id}/uploadExcel', 'AccessController@uploadExcelAccess');
    Route::get('congress/{congress_id}/user', 'AccessController@getUserAccessesByCongressId')->middleware('assign.guard:users');
    Route::get('paginantion/congress/{congress_id}', 'AccessController@getAccessesByCongressIdPginantion')->middleware('assign.guard:users');
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

// Peacksource API
Route::group(["prefix" => "peaksource"], function () {
    Route::group(["prefix" => '{congressId}'], function () {
        Route::get('users', 'CongressController@getUsersByCongressPeacksource');
        Route::get('eposters', 'SubmissionController@getEpostersByCongressPeacksource');
        Route::get('urls', 'StandController@getAllUrlsByCongressId');
        Route::get('access-stand/get-status', 'StandController@getAllAccessStandByCongressId');
        Route::post('save-score-game', 'AccessController@saveScoreGame');
        Route::get('get-score-game', 'AccessController@getScoresByCongressPeaksource');
    });
});
Route::group(['prefix' => 'congress/{congress_id}/landing-page'], function () {
    Route::get('get-config', 'CongressController@getConfigLandingPageToFrontOffice');
    Route::get('get-speakers', 'CongressController@getLandingPageSpeakersToFrontOffice');
});

// 3D API
Route::group(["prefix" => "3D"], function () {
    Route::group(['middleware' => ['assign.guard:users']], function () {
        Route::post('login', 'Auth\LoginController@login3DUser');
        Route::group(["prefix" => "congress/{congressId}"], function () {
            Route::get('booths', 'StandController@get3DBooths');
        });
    });
}); 
