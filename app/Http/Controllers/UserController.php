<?php

namespace App\Http\Controllers;

use App\Models\AttestationRequest;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OrganizationServices;
use App\Services\PackServices;
use App\Services\PaymentServices;
use App\Services\RoomServices;
use App\Services\SharedServices;
use App\Services\SmsServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $smsServices;
    protected $userServices;
    protected $congressServices;
    protected $adminServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $accessServices;
    protected $packServices;
    protected $organizationServices;
    protected $paymentServices;
    protected $mailServices;
    protected $roomServices;

    function __construct(UserServices $userServices, CongressServices $congressServices,
                         AdminServices $adminServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         AccessServices $accessServices,
                         PackServices $packServices,
                         OrganizationServices $organizationServices,
                         PaymentServices $paymentServices,
                         SmsServices $smsServices,
                         RoomServices $roomServices,
                         MailServices $mailServices)
    {
        $this->smsServices = $smsServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->accessServices = $accessServices;
        $this->packServices = $packServices;
        $this->organizationServices = $organizationServices;
        $this->paymentServices = $paymentServices;
        $this->mailServices = $mailServices;
        $this->roomServices = $roomServices;
    }

    public function getUserByTypeAndCongressId($congress_id, Request $request)
    {
        $privilegeIds = $request->all();
        return $this->userServices->getUserByTypeAndCongressId($congress_id, $privilegeIds);
    }

    public function index()
    {
        return $this->userServices->getAllUsers();
    }

    public function getUserByEmail(Request $request)
    {
        $email = $request->input('email');

        if (!$user = $this->userServices->getUserByEmail($email)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        return response()->json($user);
    }

    public function confirmInscription(Request $request, $userId)
    {
        $code = $request->query('verification_code', '');

        if (!($user = $this->userServices->getUserByVerificationCodeAndId($code, $userId)))
            return response()->json(['error' => 'user not found'], 404);

        $user->email_verified = 1;
        $user->update();

        return response()->redirectTo(UrlUtils::getBaseUrlFrontOffice() . '/login' . "?valid_account=true");
    }

    public function getUserByCongressIdAndUserId($userId, $congressId)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$admin_congress = $this->adminServices->checkHasPrivilegeByCongress($admin->admin_id, $congressId)) {
            return response()->json('no admin found', 404);
        }
        $admin_id = $admin_congress->privilege_id == 13 ? $admin->admin_id : null;
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('show_in_register', '=', 1);
            }, 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'inscription_evaluation' => function ($query) use ($congressId, $admin_id) {

                $query->select(['user_id', 'note', 'admin_id', 'commentaire'])->where('congress_id', '=', $congressId)
                    ->when($admin_id, function ($q) use ($admin_id) {
                        return $q->where('admin_id', '=', $admin_id);
                    });
            },
            'inscription_evaluation.admin' => function ($query) {
                $query->select(['admin_id', 'name']);
            },
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type', 'packs' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }
        ]);

        return response()->json($user);
    }

    public function getUserByCongressIdAndUserIdForPayement($userId, $congressId, Request $request)
    {
        $verification_code = $request->query('verification_code', '');
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('show_in_register', '=', 1);
            }, 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'user_congresses.congress.config',
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type'
        ]);
        if ($user->verification_code !== $verification_code) {
            return response()->json('bad request', 400);
        }
        return response()->json($user, 200);

    }

    public function update(Request $request, $user_id)
    {
        if (!$request->has(['first_name', 'last_name'])) {
            return response()->json([
                'response' => 'invalid request',
                'content' => [
                    'gender', 'first_name', 'last_name',
                    'profession', 'domain', 'establishment', 'city_id',
                    'address', 'postal', 'tel', 'mobile', 'fax',
                ]
            ], 400);
        }
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        return response()->json($this->userServices->updateUser($request, $user), 202);
    }

    public function delete($userId, $congressId)
    {

        $this->userServices->deleteUserAccesses($userId, $congressId);
        $this->userServices->deleteFormInputUser($userId, $congressId);
        $this->userServices->deleteUserPacks($userId, $congressId);
        $userCongress = $this->userServices->getUserCongress($congressId, $userId);
        $payment = $this->userServices->getPaymentInfoByUserAndCongress($userId, $congressId);
        $evaluations = $this->userServices->getAllEvaluationInscriptionByUserId($userId, $congressId);
        if ($userCongress) {
            $userCongress->delete();
        }
        if ($payment) {
            $payment->delete();
        }
        foreach ($evaluations as $evaluation) {
            $evaluation->delete();
        }
        return response()->json(['response' => 'user disaffected to congress'], 202);
    }

    public function validateUser($user_id, $validation_code)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($validation_code === $user->verification_code) {
            $user->email_verified = 1;
            $user->update();
            return response()->json(['response' => 'user verified'], 202);
        }
        return response()->json(['response' => 'invalid verifiaction code'], 400);
    }

    public function resendConfirmationMail($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }


        $this->userServices->sendConfirmationMail($user);
        return response()->json(['response' => 'email send to user' . $user->email], 202);
    }

    public function getUsersByCongressPagination($congressId, Request $request)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$admin_congress = $this->adminServices->checkHasPrivilegeByCongress($admin->admin_id, $congressId)) {
            return response()->json('no admin found', 404);
        }
        $perPage = $request->query('perPage', 10);
        $search = $request->query('search', '');
        $tri = $request->query('tri', '');
        $order = $request->query('order', '');
        $admin_id = $admin_congress->privilege_id == 13 ? $admin->admin_id : null;
        $users = $this->userServices->getUsersByCongress($congressId, null, true, $perPage, $search, $tri, $order, $admin_id);


        foreach ($users as $user) {
            foreach ($user->accesses as $access) {
                if ($access->pivot->isPresent == 1) {
                    $infoPresence = $this->badgeServices->getAttestationEnabled($user->user_id, $access);
                    $access->attestation_status = $infoPresence['enabled'];
                    $access->time_in_access = $infoPresence['time'];
                } else
                    $access->attestation_status = 0;
            }
        }
        return response()->json($users);
    }


    public function getUsersByCongress($congressId, $privilegeId)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersMinByCongress($congressId, $privilegeId);

        return response()->json($users);
    }

    public function changeUserStatus($user_id, $congress_id, Request $request)
    {
        if (!$user_congress = $this->userServices->getUserCongress($congress_id, $user_id)) {
            return response()->json(['messsage' => 'no user congress found'], 404);
        }
        if (!$request->has('status')) {
            return response()->json(['message' => 'status is required'], 400);
        }

        $this->userServices->changeUserStatus($user_congress, $request->input('status'));
        $user = $this->userServices->getUserById($user_id);
        $congress = $this->congressServices->getCongressById($congress_id);

        if ($request->input('status') == 1) {
            // Mail acceptation
            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $user_congress->privilege_id);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic($badge, $user, $user->qr_code, $user_congress->privilege_id);
            }
            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice), $user, $congress, $mail->object, $fileAttached, $userMail);
                }
            }
        } else if ($request->input('status') == -1) {
            // Mail refus
            if ($mailtype = $this->congressServices->getMailType('refus')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_id);
                    $this->userServices->sendMail(
                        $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, null, $userMail);
                }
            }
        }
        return response()->json(['message' => 'change status success'], 200);
    }

    public function affectScoreToUser($congress_id, $user_id, Request $request)
    {
        if (!($request->has('note') && $request->has('admin_id'))) {
            return response()->json('some fields are missing', 400);
        }
        if (!$evaluation = $this->userServices->getEvaluationInscriptionByUserIdAndAdminId(
            $user_id,
            $congress_id,
            $request->input('admin_id'))) {
            return response()->json('evaluation not found', 404);
        }

        $evaluation = $this->userServices->affectNoteToUser(
            $evaluation,
            $request->input('note'),
            $request->input('commentaire')
        );
        //affect gloable score ;
        $user_congress = $this->userServices->getUserCongress($congress_id, $user_id);
        $avg_note = $this->userServices->getAverageNote($user_id, $congress_id);
        $user_congress->globale_score = $avg_note;
        $user_congress->update();

        return response()->json('Evaluation has been updated successfully', 200);

    }

    public function getUsersByPrivilegeByCongress(Request $request, $congressId)
    {
        if (!$request->has(['privileges'])) {
            return response()->json(["error" => "privileges is required"], 400);
        }
        $privileges = $request->input('privileges');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersByCongressByPrivileges($congressId, $privileges);

        foreach ($users as $user) {
            foreach ($user->accesss as $access) {
                if ($access->pivot->isPresent == 1) {
                    $infoPresence = $this->badgeServices->getAttestationEnabled($user->user_id, $access);
                    $access->attestation_status = $infoPresence['enabled'];
                    $access->time_in_access = $infoPresence['time'];
                } else
                    $access->attestation_status = 0;
            }
        }
        return response()->json($users);
    }

    public function addUserToCongress(Request $request, $congressId)
    {

        $accessIds = $request->input("accessIds");
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $user = $this->userServices->addParticipant($request, $congressId);
        $this->userServices->affectAccess($user->user_id, $accessIds, $user->pack->accesses);

        return response()->json(['add success'], 200);
    }

    public function getAllUsersByCongress($congress_id, Request $request)
    {
        $privilegeId = $request->query('privilege_id', null);

        $users = $this->userServices->getAllUsersByCongress($congress_id, $privilegeId);

        return response()->json($users);
    }

    public function registerUser(Request $request)
    {
        if (!$request->has(['email', 'first_name', 'last_name', 'password']))
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'first_name', 'last_name', 'password']], 400);

        // Get User per mail
        if (!$user = $this->userServices->getUserByEmail($request->input('email'))) {
            $user = $this->userServices->saveUser($request);
            // TODO Sending Confirmation Mail

            if ($mailAdminType = $this->mailServices->getMailTypeAdmin('confirmation')) {
                $activationLink = $activationLink = UrlUtils::getBaseUrl() . '/users/confirmInscription/' . $user->user_id . '?verification_code=' . $user->verification_code;
                if ($mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
                    $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
                    $this->userServices->sendMail($this->adminServices->renderMail($mail->template, null, $activationLink), $user, null, $mail->object, null, $userMail);
                }
            }
        } else
            $user = $this->userServices->editUser($request, $user);
        return response()->json($user);
    }


    public function saveUserInscription(Request $request, $congress_id)
    {
        $packId = $request->input('packIds', []);
        $accessesIds = $request->input('accessesId', []);
        $privilegeId = 3;
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 404);
        }
        $congress = $this->congressServices->getCongressById($congress_id);
        if (!$congress) {
            return response()->json(['response' => 'No congress found'], 404);
        }

        // Check if User already registed to congress
        if ($user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
            return response()->json(['error' => 'user registred congress'], 405);
        }

        // Affect User to Congress
        $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, null, null);

        $this->handleCongressInscription($request, $privilegeId, $user, $congress, $congress_id, $packId, $accessesIds);

        return response()->json(['response' => 'Inscrit avec succès'], 200);
    }

    public function saveUser(Request $request, $congress_id)
    {

        if (!$request->has(['email', 'privilege_id', 'first_name', 'last_name']))
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'privilege_id', 'first_name', 'last_name']], 400);

        $privilegeId = $request->input('privilege_id');
        if ($privilegeId == 3 && !$request->has('price')) {
            return response()->json(['response' => 'bad request', 'required fields' => ['price']], 400);
        }
        //check if date limit
        // Get User per mail
        if (!$user = $this->userServices->getUserByEmail($request->input('email')))
            $user = $this->userServices->saveUser($request);
        else
            $user = $this->userServices->editUser($request, $user);

        // Check if User already registed to congress
        if ($user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
            return response()->json(['error' => 'user registred congress'], 405);
        }

        $congress = $this->congressServices->getCongressById($congress_id);
        if (!$congress) {
            return response()->json(['response' => 'No congress found'], 404);
        }
        $packIds =  $request->input('packIds', 0);
        if ( sizeof($congress->packs) > 0 && sizeof($packIds) === 0 ) {
            return response()->json('you should select at least one pack',400);
        }
        // Affect User to Congress
        $this->userServices->saveUserCongress($congress_id, $user->user_id, $request->input('privilege_id'), $request->input('organization_id'), $request->input('pack_id'));

        $packId = $request->input('packIds', 0);
        $accessesIds = $request->input('accessIds', []);
        $this->handleCongressInscription($request, $privilegeId, $user, $congress, $congress_id, $packId, $accessesIds);
        return response()->json(['response' => 'Inscrit avec succès'], 200);
    }

    public function editerUserToCongress(Request $request, $congressId, $userId)
    {
        if (!$request->has(['email', 'privilege_id', 'first_name', 'last_name']))
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'privilege_id', 'first_name', 'last_name']], 400);

        $privilegeId = $request->input('privilege_id');
        if ($privilegeId == 3 && !$request->has('price')) {
            return response()->json(['response' => 'bad request', 'required fields' => ['price']], 400);
        }

        // Get User perId
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_packs'
            , 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type'
        ]);

        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        } else {
            $this->userServices->editUser($request, $user);
        }
        //Delete Data inutile
        //$user->responses->delete();
        $this->userServices->deleteFormInputUser($userId, $congressId);

        if (sizeof($user->payments) > 0 && $request->has("price")) {
            $user->payments[0]->price = $request->input("price");
            $user->payments[0]->update();
        } else {
            if ($privilegeId == 3 && $request->input("price") != 0)
                $this->paymentServices->affectPaymentToUser($user->user_id, $congressId, $request->input("price"), false);
        }

        if ($privilegeId != 3 && sizeof($user->payments) > 0) {
            $user->payments[0]->delete();
        }

        $this->userServices->updateUserCongress($user->user_congresses[0], $request);

        //Adding Responses User To Form (Additional Information)
        if ($request->has('responses')) {
            $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);
        }

        $accessIds = $request->input('accessIds');
        //Save Access Premium
        $userAccessIds = $this->accessServices->getAccessIdsByAccess($user->accesses);

        if ($privilegeId != 3) {
            $packs = $this->packServices->getAllPackByCongress($congressId);
            $packIds = $this->packServices->getPackIdsByPacks($packs);
            $this->packServices->editUserPacksWithPackId($userId, $user->user_packs, $packIds);
            $allAccess = $this->accessServices->getMainByCongressId($congressId);
            $accessIds = $this->accessServices->getAccessIdsByAccess($allAccess);
        } else {

            $this->packServices->editUserPacksWithPackId($userId, $user->user_packs, $request->input('packIds'));
            $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 0, 0);
            $accessNotInRegisterIds = $this->accessServices->getAccessIdsByAccess($accessNotInRegister);
            $accessIds = array_merge($accessIds, $accessNotInRegisterIds);
            $accessInPackNotInRegister = $this->accessServices->getAllAccessByPackIds(
                $user->user_id,
                $congressId,
                $request->input('packIds'),
                1,
                0
            );
            $accessInPackNotInRegisterIds = $this->accessServices->getAccessIdsByAccess($accessInPackNotInRegister);
            $accessIds = array_merge($accessIds, $accessInPackNotInRegisterIds);
        }

        if ($accessIds && array_count_values($accessIds)) {
            //$accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));
            $accessDiffDeleted = array_diff($userAccessIds, $accessIds);
            $accessDiffAdded = array_diff($accessIds, $userAccessIds);
            $this->userServices->affectAccessIds($user->user_id, $accessDiffAdded);
            $this->userServices->deleteAccess($user->user_id, $accessDiffDeleted);
        } else if ($userAccessIds && array_count_values($userAccessIds)) $this->userServices->deleteAccess($user->user_id, $userAccessIds);

        return response()->json($user, 200);
    }

    public function checkUserRights($congressId, $accessId = null)
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $userId = $user->user_id;
        $user = $this->userServices->getUserByIdWithRelations($userId, ['user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        },
            'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'accesses' => function ($query) use ($congressId, $accessId) {
                $query->where('Access.access_id', '=', $accessId)->where('congress_id', '=', $congressId);
            },
            'user_access' => function ($query) use ($userId, $accessId) {
                $query->where('user_id', '=', $userId)->where('access_id', '=', $accessId);
            }]);

        $userRight = $this->userServices->checkUserRights($user, $accessId);

        if ($userRight == 2 || $userRight == 3) {
            $userToUpdate = $accessId ? $user->user_access[0] : $user->user_congresses[0];
            $roomName = $accessId ? 'eventizer_room_' . $congressId . $accessId : 'eventizer_room_' . $congressId;
            $token = $this->roomServices->createToken(
                $user->email,
                $roomName,
                $userRight == 2 ? false : true,
                $user->first_name . " " . $user->last_name
            );
            $userToUpdate->token_jitsi = $token;
            $userToUpdate->update();
            return response()->json(['response' => $userToUpdate], 200);

        } else {
            return response()->json(['response' => 'not authorized'], 401);
        }
    }

    public function getAllUserAccess($congressId)
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['message' => 'no user found'], 400);
        }
        $userId = $user->user_id;
        return $this->userServices->getAllUserAccess($congressId, $userId);
    }

    function validateUserAccount($userId = null, $congressId = null, $token = null)
    {
        $user = $this->userServices->getUserById($userId);
        if (!$user) {
            return response()->json(['response' => 'Votre compte à été supprimé'], 404);
        }
        if ($token == $user->verification_code) {
            $user->email_verified = 1;
            $user->update();

            return response()->redirectTo(UrlUtils::getUrlEventizerWeb() . "/#/auth/user/" . $user->user_id . "/upload-payement?token=" . $token . "&congressId=" . $congressId);
        } else {
            return response()->json(['response' => 'Token not match'], 400);
        }
    }

    public function getUsersByAccess($congressId, $accessId)
    {
        $users = $this->userServices->getUsersByAccess($congressId, $accessId);

        return response()->json($users);
    }

    public function getPresencesByAccess($accessId)
    {
        $users = $this->userServices->getPresencesByAccess($accessId);

        return response()->json($users);
    }

    public function getPresencesByCongress($congressId)
    {
        $users = $this->userServices->getAllPresencesByCongress($congressId);

        return response()->json($users);
    }

    public function getQrCodeUser($userId)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(["error" => "user not found"], 404);
        }
        $file = new Filesystem();

        Utils::generateQRcode($user->qr_code, "qrcode.png");

        if ($file->exists(public_path() . "/qrcode.png")) {
            return response()->download(public_path() . "/qrcode.png")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }
    }


    public function getAllPayementTypes()
    {
        return response()->json($this->paymentServices->getAllPaymentTypes());
    }


    public function getUserStatusPresences($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $autorisation = $request->input('autorisation');
        $accessId = $request->input("accessId");
        if ($accessId) {
            if (!$access = $this->accessServices->getById($accessId)) {
                return response()->json(['error' => 'access not found'], 404);
            }
            $usersAccess = $this->accessServices->getUserAccessByAccessId($accessId);
            $result = array();
            foreach ($usersAccess as $user) {
                /*if ($user->isPresent == $autorisation)
                    array_push($result, $user);*/

                //TODO return after congress
                if ($this->badgeServices->getAttestationEnabled($user->user_id, $access)['enabled'] == $autorisation) {
                    array_push($result, $user);
                }
            }
            return response()->json($result);
        } else {
            $userCongress = $this->congressServices->getUsersByStatus($congressId, $autorisation);
            return response()->json($userCongress);
        }
    }

    public function changePaiement($paymentId, Request $request)
    {
        $isPaid = $request->input('status');

        if (!$userPayement = $this->userServices->getPaymentById($paymentId)) {
            return response()->json(['error' => 'payment not found'], 404);
        }

        $congressId = $userPayement->congress_id;
        if (!$user = $this->userServices->getUserByIdWithRelations($userPayement->user_id, ['accesses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($userPayement->congress_id);

        $userCongress = $this->userServices->getUserCongress($congress->congress_id, $user->user_id);

        if ($userPayement->isPaid != 1 && $isPaid == 1) {
            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $userCongress->privilege_id);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic(
                    $badge,
                    $user,
                    $user->qr_code,
                    $userCongress->privilege_id
                );
            }

            // $link = Utils::baseUrlWEB . "/#/auth/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
            /*if ($mailtype = $this->congressServices->getMailType('paiement')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayement), $user, $congress, $mail->object, null, $userMail);
                }
            }*/

            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayement, null, $linkFrontOffice), $user, $congress, $mail->object, $fileAttached, $userMail);
                }
            }
            $this->smsServices->sendSms($congress->congress_id, $user, $congress);
        }
        $userPayement->isPaid = $isPaid;
        $userPayement->update();

        return response()->json(['message' => 'user updated success']);
    }

    public function uploadUsers(Request $request)
    {

        ini_set('max_execution_time', 500);
        $savedUsers = array();
        $users = $request->input("data");
        foreach ($users as $userData) {
            if ($userData['first_name'] && $userData['last_name'] && $userData['mobile'] && $userData['email']) {
                $request->merge([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'mobile' => $userData['mobile']
                ]);
                if (!$user = $this->userServices->getUserByEmail($userData['email'])) {
                    $user = $this->userServices->saveUser($request);
                    array_push($savedUsers, $user->user_id);
                } else {
                    array_push($savedUsers, $user->user_id);
                }
            }
        }
        return $savedUsers;
    }

    public function saveUsersFromExcel($congressId, Request $request)
    {
        ini_set('max_execution_time', 500); //3 minutes

        $congress = $this->congressServices->getById($congressId);
        $users = $request->input("data");
        $refused = $request->query('refused');
        if ($refused == "false") {
            $refused = false;
        } else {
            $refused = true;
        }
        //PrivilegeId = 3
        $sum = 0;
        $privilegeId = $request->input("privilegeId");
        $organizationId = $request->input("organisationId");
        $emails = [];
        $accessIdTable = [];
        foreach ($users as $e) {
            $emails[] = $e["EMAIL"];
            $accessIdTable[] = $e["accessIdTable"];
        }

        // Affect All Access Free (To All Users)
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 0);
        $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 1);
        $accessIds = $this->accessServices->getAccessIdsByAccess($accessNotInRegister);
        foreach ($users as $userData) {
            if ($userData['EMAIL']) {

                $request->merge(['privilege_id' => $privilegeId,
                    'email' => $userData['EMAIL']
                ]);
                // Get User per mail
                if ($user_by_mail = $this->userServices->getUserByEmail($userData['EMAIL'])) {
                    $user_id = $user_by_mail->user_id;
                    $user = $this->userServices->getUserByIdWithRelations($user_id, [
                        'accesses' => function ($query) use ($congressId) {
                            $query->where('congress_id', '=', $congressId);
                            $query->where('show_in_register', '=', 1);
                        }, 'payments' => function ($query) use ($congressId) {
                            $query->where('congress_id', '=', $congressId);
                        },
                        'user_congresses' => function ($query) use ($congressId) {
                            $query->where('congress_id', '=', $congressId);
                        }
                    ]);
                    // Check if User already registed to congress
                    $user_congress = $this->userServices->getUserCongress($congressId, $user->user_id);
                    if (!$user_congress) {
                        $user_congress = $this->userServices->saveUserCongress($congressId, $user->user_id, $request->input('privilege_id'), $request->input('organization_id'), $request->input('pack_id'));
                        $this->paymentServices->affectPaymentToUser($user->user_id, $congressId, 0, false);
                        $this->paymentServices->changeIsPaidStatus($user->user_id, $congressId, 1);
                    } else {
                        $user_congress->privilege_id = $privilegeId;
                        $user_congress->update();
                        $this->paymentServices->changeIsPaidStatus($user->user_id, $congressId, 1);
                    }

                    $new_access_array = null;
                    $old_access_id_array = [];
                    $old_access_array = $user->accesses;
                    for ($i = 0; $i < sizeof($emails); $i++) {
                        //if statement to get the right index i of accessIdTable corresponding to our user 
                        if ($emails[$i] == $userData['EMAIL']) {
                            //put all new accesses ID in the new access array 
                            $new_access_array = $accessIdTable[$i];
                        };
                    }
                    if ($accessNotInRegister) {
                        $this->userServices->affectAccessIds($user->user_id, $accessNotInRegister);
                    }
                    if ($new_access_array) {
                        //add new accesses if not already existant
                        for ($j = 0; $j < sizeof($new_access_array); $j++) {
                            $exists = false;
                            //check if the user already has this access_id
                            foreach ($old_access_array as $old_access) {
                                if ($old_access->access_id == $new_access_array[$j]) {
                                    $exists = true;
                                }
                            }
                            if (!$exists) {
                                // this means we have a new access to add
                                // add the new access
                                $access_added = $this->userServices->affectAccessById($user->user_id, $new_access_array[$j]);
                            }
                            $this->paymentServices->changeIsPaidStatus($user->user_id, $congressId, 1);

                        }
                        // send mail confirmation
                        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                            $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice), $user, $congress, $mail->object, null, $userMail);
                            }
                        }
                        //delete the access if no longer exists on the excel sheet
                        // we loop in the old access aray
                        for ($j = 0; $j < sizeof($old_access_array); $j++) {
                            $exists = false; // initialise to doesn't exist
                            for ($k = 0; $k < sizeof($new_access_array); $k++) {
                                //search if access exists in the new access table if it does we won't delete it
                                //otherwise we have to delete it
                                if ($old_access_array[$j]->access_id == $new_access_array[$k]) {
                                    $exists = true; // access exists in both array new and old
                                }
                            }
                            if (!$exists) {
                                //delete where access_id== $j from the old access array
                                $this->userServices->deleteAccessById($user->user_id, $old_access_array[$j]->access_id);
                            }
                        }
                    } else {
                        //new access_array empty
                        //there is no new accesses
                        //delete all current access traitement
                        for ($k = 0; $k < sizeof($old_access_array); $k++) {
                            $this->userServices->deleteAccessById($user->user_id, $old_access_array[$k]->access_id);
                        }
                    }

                }
            }
        }

        if ($refused && $congress->congress_type_id == 2) {
            // partie gestion des participants refusés !
            $all_refused_participants = $this->userServices->getRefusedParticipants($congressId, $emails);
            foreach ($all_refused_participants as $refused_participant) {
                //change user payment status
                $this->paymentServices->changeIsPaidStatus($refused_participant->user_id, $congressId, -1);
                //envoi de mail de refus
                if ($mailtype = $this->congressServices->getMailType('refus')) {
                    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $refused_participant->user_id);
                        $this->userServices->sendMail(
                            $this->congressServices->renderMail($mail->template, $congress, $refused_participant, null, null, null),
                            $refused_participant,
                            $congress,
                            $mail->object,
                            null,
                            $userMail
                        );
                    }
                }
            }
        }

        if ($organizationId != null) {
            $congressOrganization = $this->organizationServices->getOrganizationByCongressIdAndOrgId($congressId, $organizationId);
            $congressOrganization->montant = $congressOrganization->montant + $sum;
            $congressOrganization->update();

            return response()->json($this->organizationServices->getAllUserByOrganizationId($organizationId, $congressId));
        } else
            return response()->json(['message' => 'import success']);
    }


    public function redirectToLinkFormSondage($userId, $congressId)
    {
        /* Meme Block Of Send Attestation */
        if (!$user = $this->userServices->getUserByIdWithRelations($userId, ['accesses' => function ($query) use ($congressId) {
            $query->where("congress_id", "=", $congressId);
            $query->where('with_attestation', "=", 1);
        }, 'user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($congressId);
        $request = array();
        if ($user->email != null && $user->email != "-" && $user->email != "") {
            if (sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->isPresent == 1 && $congress->attestation) {
                array_push(
                    $request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false
                    )
                );
            }
            foreach ($user->accesses as $access) {
                if ($access->pivot->isPresent == 1) {
                    if (sizeof($access->attestations) > 0) {
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, 3);
                        if ($attestationId) {
                            array_push(
                                $request,
                                array(
                                    'badgeIdGenerator' => $attestationId,
                                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                                    'qrCode' => false
                                )
                            );
                        }
                    }
                }
                $chairPerson = $this->accessServices->getChairAccessByAccessAndUser($access->access_id, $userId);
                $privilegeId = null;
                if ($chairPerson) {
                    $privilegeId = 5;
                }
                $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($access->access_id, $userId);
                if ($speakerPerson) {
                    $privilegeId = 8;
                }
                $attestationId = null;
                if ($privilegeId)
                    $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, $privilegeId);
                if ($attestationId) {
                    array_push(
                        $request,
                        array(
                            'badgeIdGenerator' => $attestationId,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false
                        )
                    );
                }
            }
            $mailtype = $this->congressServices->getMailType('attestation');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->badgeServices->saveAttestationsInPublic($request);
                $this->userServices->sendMailAttesationToUser(
                    $user,
                    $congress,
                    $userMail,
                    $mail->object,
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null)
                );
            }
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }


        /* Block Sending Sondage */
        $linkForm = $congress->config->link_sondage;

        return response()->redirectTo($linkForm);
    }

    public function sendSondage($userId, $congressId)
    {

        if (!$user = $this->userServices->getUserByIdWithRelations($userId, [])) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $congress = $this->congressServices->getCongressById($congressId);

        if ($user->email != null && $user->email != "-" && $user->email != "") {

            $mailtype = $this->congressServices->getMailType('sondage');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            $linkSondage = UrlUtils::getBaseUrl() . "/users/" . $user->user_id . '/congress/' . $congressId . '/sondage';
            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->userServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, $linkSondage),
                    $user,
                    $congress,
                    $mail->object,
                    false,
                    $userMail
                );
            }
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }
        return response()->json(['message' => 'email sended success']);
    }

    public function sendMailAttesation($userId, $congressId, $strict = 1)
    {
        // $strict = 0;
        if (!$user = $this->userServices->getUserByIdWithRelations($userId, ['accesses' => function ($query) use ($congressId) {
            $query->where("congress_id", "=", $congressId);
            $query->where('with_attestation', "=", 1);
        }, 'user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($congressId);
        $request = array();
        if ($user->email != null && $user->email != "-" && $user->email != "") {
            if (sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->isPresent == 1 && $congress->attestation) {
                array_push(
                    $request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false
                    )
                );
            }
            foreach ($user->accesses as $access) {
                if ($strict == 0 || $access->pivot->isPresent == 1) {
                    if (sizeof($access->attestations) > 0) {
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, 3);
                        if ($attestationId) {
                            array_push(
                                $request,
                                array(
                                    'badgeIdGenerator' => $attestationId,
                                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                                    'qrCode' => false
                                )
                            );
                        }
                    }
                }
                $chairPerson = $this->accessServices->getChairAccessByAccessAndUser($access->access_id, $userId);
                $privilegeId = null;
                if ($chairPerson) {
                    $privilegeId = 5;
                }
                $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($access->access_id, $userId);
                if ($speakerPerson) {
                    $privilegeId = 8;
                }
                $attestationId = null;
                if ($privilegeId)
                    $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, $privilegeId);
                if ($attestationId) {
                    array_push(
                        $request,
                        array(
                            'badgeIdGenerator' => $attestationId,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false
                        )
                    );
                }
            }

            $mailtype = $this->congressServices->getMailType('attestation');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->badgeServices->saveAttestationsInPublic($request);
                $this->userServices->sendMailAttesationToUser(
                    $user,
                    $congress,
                    $userMail,
                    $mail->object,
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null)
                );
            }
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }
        return response()->json(['message' => 'email sended success']);
    }

    public function uploadPayement($userId, $congressId, Request $request)
    {
        if (!$paymentUser = $this->userServices->getPaymentByUserId($congressId, $userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $paymentUser = $this->userServices->uploadPayement($paymentUser, $request);

        $user = $this->userServices->getUserById($userId);

        if ($mailtype = $this->congressServices->getMailType('upload')) {
            if ($mail = $this->congressServices->getMail($paymentUser->congress_id, $mailtype->mail_type_id)) {
                $congress = $this->congressServices->getCongressById($paymentUser->congress_id);
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $paymentUser->user_id);
                $this->userServices->sendMail(
                    $this->congressServices
                        ->renderMail($mail->template, $congress, $user, null, null, null),
                    $user,
                    $congress,
                    $mail->object,
                    false,
                    $userMail
                );
            }
        }

        return response()->json($user);
    }

    public function calculPrice($congress, $packId, $accessIds)
    {
        $price = $congress->price;
        if ($packId) {
            $pack = $this->packServices->getPackById($packId);
            $price += $pack->price;
        }
        $accesss = $this->accessServices->getAllAccessByAccessIds($accessIds);
        if (count($accesss))
            $price += array_sum(array_map(function ($access) {
                return $access["price"];
            }, $accesss->toArray()));
        return $price;
    }

    public function sendCustomMail($user_id, $mail_id)
    {
        if (!$user = $this->userServices->getParticipatorById($user_id))
            return response()->json(['response' => 'user not found'], 404);
        if (!$mail = $this->congressServices->getEmailById($mail_id))
            return response()->json(['response' => 'mail not found'], 404);
        $congress = $this->congressServices->getCongressById($user->congress_id);
        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, false);
        return response()->json(['response' => 'success'], 200);
    }

    function userConnect($qrCode)
    {
        $user = $this->userServices->getUserByQrCode($qrCode);
        return $user ? response()->json($user, 200, []) : response()->json(["error" => "wrong qrcode"], 404);
    }

    function userConnectPost(Request $request)
    {
        if (!$request->has('qr_code')) {
            return response()->json(['error' => 'bad reques'], 400);
        }

        $user = $this->userServices->getUserByQrCode($request->qr_code);

        $request->merge(['email' => $user->email, 'password' => $user->passwordDecrypt]);

        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    function getPresenceStatus($user_id)
    {
        $table = [];
        foreach ($this->userServices->getUserById($user_id)->accesss as $access) {
            array_push($table, $access->pivot);
        }
        return $table;
    }

    function getAllPresenceStatus(Request $request)
    {
        $table = [];
        foreach ($request->all() as $user_id) {
            $table = array_merge($table, $this->getPresenceStatus($user_id));
        }
        return $table;
    }

    function requestAttestations(Request $request, $user_id)
    {
        if (!$this->userServices->getUserById($user_id)) return response()->json(['error' => 'user_does_not_exist'], 404);
        $res = [];
        $oldRequests = $this->userServices->getAttestationRequestsByUserId($user_id);
        foreach ($request->all() as $access_id) {
            if (!$this->userServices->isRegisteredToAccess($user_id, $access_id)) continue;
            $already_exists = false;
            foreach ($oldRequests as $oldRequest) {
                if ($oldRequest->access_id == $access_id) {
                    $already_exists = true;
                    array_push($res, $oldRequest);
                }
            }
            if ($already_exists) continue;
            $attestation_request = new AttestationRequest();
            $attestation_request->access_id = $access_id;
            $attestation_request->user_id = (int)$user_id;
            $attestation_request->save();
            array_push($res, $attestation_request);
        }
        return response()->json($res, 200);
    }

    function requestedAttestations(Request $request)
    {
        $res = [];
        foreach ($request->all() as $user_id) {
            $temp = $this->userServices->getAttestationRequestsByUserId($user_id);
            if ($temp && count($temp)) $res = array_merge($res, $temp);
        }
        return $res;
    }

    public function setAttestationRequestStatus($user_id, $done)
    {
        $requests = $this->userServices->getAttestationRequestsByUserId($user_id);
        foreach ($requests as $req) {
            $req->done = $done ? 1 : 0;
            $req->update();
        }
        return $this->userServices->getAttestationRequestsByUserId($user_id);
    }

    public function changeQrCode($user_id, Request $request)
    {
        $congressId = $request->input("congressId");

        if (!$user = $this->userServices->getUserByIdWithRelations($user_id, ['user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }]))
            return response()->json(['error' => 'user not found'], 400);

        $oldUsers = $this->userServices->getMinUserByQrCode($request->input("qrcode"));

        foreach ($oldUsers as $oldUser) {
            if ($oldUser->user_id != $user->user_id) {
                $oldUser->qr_code = Utils::generateCode($oldUser->user_id);
                $oldUser->update();
            }
        }

        if (sizeof($user->user_congresses) > 0) {
            $user->user_congresses[0]->isPresent = 1;
            $user->user_congresses[0]->update();
        }

        $user->qr_code = $request->get('qrcode');
        $user->update();
        return $user;
    }

    public function mobileEditUser(Request $request, $user_id)
    {
        if (!$request->has(['first_name', 'last_name', 'gender', 'mobile', 'email', 'country_id']))
            return response()->json(['error' => 'bad request'], 400);
        $user = $this->userServices->getUserById($user_id);
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->gender = $request->input('gender');
        $user->mobile = $request->input('mobile');
        $user->email = $request->input('email');
        $user->country_id = $request->input('country_id');
        $user->update();
        return $user;
    }

    public function uploadProfilePic(Request $request, $user_id)
    {
        if (!$user = $this->userServices->getUserById($user_id)) return response()->json(['response' => 'user not found'], 404);
        return $this->userServices->uploadProfilePic($request->file('file_data'), $user);
    }

    public function getProfilePic($user_id)
    {
        if (!$user = $this->userServices->getUserById($user_id)) return response()->json(['response' => 'user not found'], 404);
        if (!$user->profile_pic) return response()->json(['response' => 'no profile pic'], 400);
        return Storage::download($user->profile_pic);
    }

    public function forgetPassword(Request $request)
    {
        if (!$request->has(['email']))
            return response()->json(['response' => 'bad request', 'required fields' => ['email']], 400);

        if (!$user = $this->userServices->getUserByEmail($request->input('email'))) {
            return response()->json(['response' => 'email not found'], 404);
        }

        if (!$mailAdminType = $this->mailServices->getMailTypeAdmin('forget_password')) {
            return response()->json(['response' => 'bad request'], 400);
        }

        if (!$mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
            return response()->json(['response' => 'bad request'], 400);
        }
        $user->verification_code = Str::random(40);
        $user->update();

        $activationLink = UrlUtils::getBaseUrlFrontOffice() . 'password/reset/' . $user->user_id . '?verification_code=' . $user->verification_code . '&user_id=' . $user->user_id;
        $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
        $this->userServices->sendMail($this->adminServices->renderMail($mail->template, null, null, $activationLink), $user, null, $mail->object, null, $userMail);

        return response()->json(['response' => 'Check your mail to reset password !'], 200);

    }

    public function getUserById($user_id, Request $request)
    {

        $verification_code = $request->query('verification_code', '');
        if (!$user = $this->userServices->getUserById($user_id)) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($user->verification_code !== $verification_code) {
            return response()->json('bad request', 400);
        }

        return response()->json($user, 200);
    }

    public function resetUserPassword($userId, Request $request)
    {
        if (!$request->has(['verification_code', 'password']))
            return response()->json(['response' => 'bad request'], 400);
        $verification_code = $request->input('verification_code');
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($user->verification_code !== $verification_code) {
            return response()->json(['response' => 'bad request'], 400);
        }
        if (!$mailAdminType = $this->mailServices->getMailTypeAdmin('reset_password_success')) {
            return response()->json(['response' => 'bad request'], 400);
        }

        if (!$mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
            return response()->json(['response' => 'bad request'], 400);
        }
        $password = $request->input('password');
        $user->passwordDecrypt = $password;
        $user->password = bcrypt($password);
        $user->update();
        $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
        $this->userServices->sendMail($this->adminServices->renderMail($mail->template), $user, null, $mail->object, null, $userMail);

        return response()->json(['response' => 'password successfully updated'], 200);
    }


    private function handleCongressInscription(Request $request, $privilegeId, $user, $congress, $congress_id, $packId, $accessesIds)
    {

        if ($request->has('responses')) {
            $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);
        }
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 0, 0);
        $this->userServices->affectAccessElement($user->user_id, $accessNotInRegister);

        if ($privilegeId == 3) {
            $this->userServices->affectPacksToUser($user->user_id, $packId);
            $accessInPackNotInRegister = $this->accessServices->getAllAccessByPackIds(
                $user->user_id,
                $congress_id,
                $packId,
                1,
                0
            );
            $this->userServices->affectAccessElement($user->user_id, $accessInPackNotInRegister);
            $this->userServices->affectAccess($user->user_id, $accessesIds, []);
        } else {
            $packs = $this->packServices->getAllPackByCongress($congress_id);
            $this->userServices->affectPacksToUser($user->user_id, null, $packs);
            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 0, 1);
            $this->userServices->affectAccessElement($user->user_id, $accessInRegister);

            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 1);
            $this->userServices->affectAccessElement($user->user_id, $accessInRegister);
        }

        if ($packId == []) {
            $pack = null;
        } else {
            $pack = $this->packServices->getPackById($packId);
        }
        $accessesId = $accessesIds;
        $accesses = $this->accessServices->getAllAccessByAccessIds($accessesId);
        $totalPrice = $this->userServices->calculateCongressFees($congress, $pack, $accesses);
        $isFree = false;
        if ($privilegeId == 3) {
            $nbParticipants = $this->congressServices->getParticipantsCount($congress_id, 3, null);
            $freeNb = $this->paymentServices->getFreeUserByCongressId($congress_id);
            //Free Inscription (By Chance)
            if ($freeNb < $congress->config->free && ($nbParticipants % 10) == 0) {
                $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $totalPrice, true);
                $isFree = true;
            }
        }
        // Sending Mail
        $link = $request->root() . "/api/users/" . $user->user_id . '/congress/' . $congress_id . '/validate/' . $user->verification_code;
        $user = $this->userServices->getUserIdAndByCongressId($user->user_id, $congress_id);
        $userPayment = null;

        if ($privilegeId != 3 || $congress->congress_type_id == 3 || ($congress->congress_type_id == 1 && $totalPrice == 0) || $isFree) {
            //Free Mail
            if ($isFree) {
                if ($mailtype = $this->congressServices->getMailType('free')) {
                    if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, false, $userMail);
                    }
                }
            }
            //Confirm Direct
            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $privilegeId);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic(
                    $badge,
                    $user,
                    $user->qr_code,
                    $privilegeId
                );
            }
            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice), $user, $congress, $mail->object, $fileAttached, $userMail);
                }
            }
            $this->smsServices->sendSms($congress_id, $user, $congress);
        } else {
            //PreInscription First (Payment Required)
            //Add Payement Ligne
            if (($congress->congress_type_id == 1 && (!$congress->config_selection)) || ($congress->congress_type_id == 1 && $congress->config_selection && ($congress->config_selection->selection_type == 2 || $congress->config_selection->selection_type == 3))) {
                $userPayment = $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $totalPrice, false);
            }
            if ($mailtype = $this->congressServices->getMailType('inscription')) {
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, $link, null, $userPayment), $user, $congress, $mail->object, false, $userMail);
                }
            }
        }
        if ($congress->config_selection && $congress->config_selection->num_evaluators>0 && $privilegeId == 3 && ($congress->congress_type_id == 2 || ($congress->congress_type_id == 1 && $congress->config_selection))) {
            $evalutors = $this->adminServices->getEvaluatorsByCongress($congress_id, 13, 'evaluations');
            $this->adminServices->affectEvaluatorsToUser(
                $evalutors,
                $congress->config_selection->num_evaluators,
                $congress_id,
                $user->user_id
            );
        }

        // Notify Organizer Mail Rule (privilege ==3 & configCongress Activated & form user-register not backoffice add)
        if ($privilegeId === 3 && $congress->config->replyto_mail && $congress->config->is_notif_register_mail && !$user->is_admin_created) {
            $mail = $congress->config->replyto_mail; // Mail To Send with every inscription
            $template = Utils::getDefaultMailNotifNewRegister();
            $objectMail = "Nouvelle Inscription";
            $this->adminServices->sendMail($this->congressServices->renderMail($template, $congress, $user, null, null, $userPayment), $congress, $objectMail, null, false, $mail);
        }
    }

}
