<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\AttestationRequest;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OrganizationServices;
use App\Services\PackServices;
use App\Services\PaymentServices;
use App\Services\SharedServices;
use App\Services\SmsServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    function __construct(UserServices $userServices, CongressServices $congressServices,
                         AdminServices $adminServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         AccessServices $accessServices,
                         PackServices $packServices,
                         OrganizationServices $organizationServices,
                         PaymentServices $paymentServices,
                         SmsServices $smsServices,
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

    public function getUserByCongressIdAndUserId($userId, $congressId)
    {

        $user = $this->userServices->getUserByIdWithRelations($userId, ['accesses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
            $query->where('show_in_register', '=', 1);
        }, 'payments' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        },
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type']);

        return response()->json($user);

    }

    public function getUserById($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }

        return response()->json($user, 200);
    }

    public function update(Request $request, $user_id)
    {
        if (!$request->has(['first_name', 'last_name'])) {
            return response()->json(['response' => 'invalid request',
                'content' => ['gender', 'first_name', 'last_name',
                    'profession', 'domain', 'establishment', 'city_id',
                    'address', 'postal', 'tel', 'mobile', 'fax',]], 400);
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
        $userCongress = $this->userServices->getUserCongress($congressId, $userId);
        $payment = $this->userServices->getPaymentInfoByUserAndCongress($userId, $congressId);
        if ($userCongress) {
            $userCongress->delete();
        }
        if ($payment) {
            $payment->delete();
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

        $perPage = $request->query('perPage', 10);
        $search = $request->query('search', '');
        $tri = $request->query('tri', '');
        $order = $request->query('order', '');
        $users = $this->userServices->getUsersByCongress($congressId, null, true, $perPage, $search, $tri, $order);


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
        } else
            $user = $this->userServices->editUser($request, $user);

        return response()->json($user);
    }

    public function saveUser(Request $request, $congress_id)
    {
        if (!$request->has(['email', 'privilege_id', 'first_name', 'last_name', 'password']))
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'privilege_id', 'first_name', 'last_name', 'password']], 400);

        $privilegeId = $request->input('privilege_id');
        if ($privilegeId == 3 && !$request->has('price')) {
            return response()->json(['response' => 'bad request', 'required fields' => ['price']], 400);
        }

        // Get User per mail
        if (!$user = $this->userServices->getUserByEmail($request->input('email')))
            $user = $this->userServices->saveUser($request);
        else
            $user = $this->userServices->editUser($request, $user);

        // Check if User already registed to congress
        if ($user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
            return response()->json(['error' => 'user registred congress'], 405);
        }

        // Affect User to Congress
        $this->userServices->saveUserCongress($congress_id, $user->user_id, $request);

        //Adding Responses User To Form (Additional Information)
        if ($request->has('responses')) {
            $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);
        }

        // Affect All Access Free (To All Users)
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 0);

        $this->userServices->affectAccessElement($user->user_id, $accessNotInRegister);

        //Save Access Premium
        if ($privilegeId == 3) {
            $this->userServices->affectAccess($user->user_id, $request->input('accessIds'), []);
        } else {
            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 1);
            $this->userServices->affectAccessElement($user->user_id, $accessInRegister);
        }

        $congress = $this->congressServices->getCongressById($congress_id);
        $isFree = false;
        if ($privilegeId == 3) {
            $nbParticipants = $this->congressServices->getParticipantsCount($congress_id, 3, null);
            $freeNb = $this->paymentServices->getFreeUserByCongressId($congress_id);
            //Free Inscription (By Chance)
            if ($freeNb < $congress->config->free && ($nbParticipants % 10) == 0) {
                $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $request->input("price"), true);
                $isFree = true;
            }
        }
        // Sending Mail
        $link = $request->root() . "/api/users/" . $user->user_id . '/congress/' . $congress_id . '/validate/' . $user->verification_code;
        $user = $this->userServices->getUserIdAndByCongressId($user->user_id, $congress_id, true);
        $userPayment = null;
        if ($privilegeId != 3 || $congress->congress_type_id == 3 || ($congress->congress_type_id == 1 && !$congress->config->has_payment) || $isFree) {
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
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $privilegeId);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
            }
            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, $linkFrontOffice), $user, $congress, $mail->object, $fileAttached, $userMail);
                }
            }
            $this->smsServices->sendSms($congress_id, $user, $congress);
        } else {
            //PreInscription First (Payment Required)
            //Add Payement Ligne
            $userPayment = $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $request->input("price"), false);

            if ($mailtype = $this->congressServices->getMailType('inscription')) {
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, $link, null, $userPayment), $user, $congress, $mail->object, false, $userMail);
                }
            }
        }

        // Notify Organizer Mail Rule (privilege ==3 & configCongress Activated & form user-register not backoffice add)
        if ($privilegeId === 3 && $congress->config->replyto_mail && $congress->config->is_notif_register_mail && !$user->is_admin_created) {
            $mail = $congress->config->replyto_mail; // Mail To Send with every inscription
            $template = Utils::getDefaultMailNotifNewRegister();
            $objectMail = "Nouvelle Inscription";
            $this->adminServices->sendMail($this->congressServices->renderMail($template, $congress, $user, null, null, $userPayment), $congress, $objectMail, null, false, $mail);
        }
        return $user;

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
        $user = $this->userServices->getUserByIdWithRelations($userId, ['accesses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
            $query->where('show_in_register', '=', 1);
        }, 'payments' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }, 'user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }, 'responses.form_input' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type']);

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
            if ($privilegeId == 3)
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
            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 1);
            $accessIds = $this->accessServices->getAccessIdsByAccess($accessInRegister);
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
            $query->where('show_in_register', '=', 1);
        }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($userPayement->congress_id);

        $userCongress = $this->userServices->getUserCongress($congress->congress_id, $user->user_id);

        if ($userPayement->isPaid != 1 && $isPaid == 1) {
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $userCongress->privilege_id);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
            }

            // $link = Utils::baseUrlWEB . "/#/auth/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
            /*if ($mailtype = $this->congressServices->getMailType('paiement')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayement), $user, $congress, $mail->object, null, $userMail);
                }
            }*/

            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayement,null ,$linkFrontOffice), $user, $congress, $mail->object, $fileAttached, $userMail);
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
                $request->merge(['first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'mobile' => $userData['mobile']]);
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
        //PrivilegeId = 3
        $sum = 0;
        $privilegeId = $request->input("privilegeId");
        $organizationId = $request->input("organisationId");

        // Affect All Access Free (To All Users)
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 0);
        $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 1);
        $accessIds = $this->accessServices->getAccessIdsByAccess($accessNotInRegister);
        foreach ($users as $userData) {
            if ($userData['email'] && $userData['first_name'] && $userData['last_name']) {

                $request->merge(['privilege_id' => $privilegeId, 'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email']]);
                // Get User per mail
                if (!$user = $this->userServices->getUserByEmail($userData['email'])) {
                    $user = $this->userServices->saveUser($request);
                }
                // Check if User already registed to congress
                $user_congress = $this->userServices->getUserCongress($congressId, $user->user_id);
                if (!$user_congress) {
                    $user_congress = $this->userServices->saveUserCongress($congressId, $user->user_id, $request);
                } else {
                    $user_congress->privilege_id = $privilegeId;
                    $user_congress->update();
                }

                if ($organizationId != null) {
                    $user_congress->organization_id = $organizationId;
                    $user_congress->organization_accepted = true;
                    $user_congress->update();
                }

                $this->userServices->deleteAccess($user->user_id, $accessIds);
                $this->userServices->affectAccessElement($user->user_id, $accessNotInRegister);

                if ($privilegeId != 3) {
                    $this->userServices->affectAccessElement($user->user_id, $accessInRegister);
                }

                if ($privilegeId == 3 && !$userPayment = $this->userServices->getPaymentInfoByUserAndCongress($user->user_id, $congressId)) {
                    $userPayment = $this->paymentServices->affectPaymentToUser($user->user_id, $congressId, $congress->price, false);
                    $sum += $userPayment->price;
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
                array_push($request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false
                    ));
            }
            foreach ($user->accesses as $access) {
                if ($access->pivot->isPresent == 1) {
                    if (sizeof($access->attestations) > 0) {
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, 3);
                        if ($attestationId) {
                            array_push($request,
                                array(
                                    'badgeIdGenerator' => $attestationId,
                                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                                    'qrCode' => false
                                ));
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
                    array_push($request,
                        array(
                            'badgeIdGenerator' => $attestationId,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false
                        ));
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
                $this->userServices->sendMailAttesationToUser($user, $congress, $userMail, $mail->object,
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null));

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

                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, $linkSondage),
                    $user, $congress, $mail->object, false, $userMail);

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
                array_push($request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false
                    ));
            }
            foreach ($user->accesses as $access) {
                if ($strict == 0 || $access->pivot->isPresent == 1) {
                    if (sizeof($access->attestations) > 0) {
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, 3);
                        if ($attestationId) {
                            array_push($request,
                                array(
                                    'badgeIdGenerator' => $attestationId,
                                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                                    'qrCode' => false
                                ));
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
                    array_push($request,
                        array(
                            'badgeIdGenerator' => $attestationId,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false
                        ));
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
                $this->userServices->sendMailAttesationToUser($user, $congress, $userMail, $mail->object,
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null));

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
                $this->userServices->sendMail($this->congressServices
                    ->renderMail($mail->template, $congress, $user, null, null, null),
                    $user, $congress, $mail->object, false, $userMail);


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
        if ($request->qr_code) {
            $user = $this->userServices->getUserByQrCode($request->qr_code);
            return $user ? response()->json($user, 200, []) : response()->json(["error" => "wrong qrcode"], 404);
        }

        $validateData = Validator::make($request->all(), [
            'email' => 'required',
            'code' => 'required',
        ]);

        if ($validateData->fails()) return response()->json(['response' => 'bad request', 'required fields' => ['email', 'code']], 400);

        $user = $this->userServices->getUserByEmailAndCode($request->email, $request->code);
        if (!$user) {
            return response()->json(["error" => "wrong credentials"], 401);
        }
        return response()->json($user);
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

        foreach ($oldUsers as $oldUser
        ) {
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
}
