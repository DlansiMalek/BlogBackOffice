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
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

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
                         MailServices $mailServices)
    {
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

    public function getUserByTypeAndCongressId($congress_id, $privilege_id)
    {
        return $this->userServices->getUserByTypeAndCongressId($congress_id, $privilege_id);
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

    public function getUserById($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($user->pack) {
            $accesses = [];
            foreach ($user->accesss as $access) {
                $inPack = false;
                foreach ($user->pack->accesses as $packAccess) {
                    if ($packAccess->access_id == $access->access_id) {
                        $inPack = true;
                        break;
                    }
                }
                if (!$inPack) array_push($accesses, $access);
            }
            $user->packlessAccesses = $accesses;
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


    public function getUsersByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersByCongress($congressId);

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

    public function saveUser(Request $request, $congress_id)
    {
        if (!$request->has(['email', 'privilege_id', 'first_name', 'last_name']))
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'privilege_id', 'first_name', 'last_name']], 400);


        $privilegeId = $request->input('privilege_id');
        if ($privilegeId == 3 && !$request->has('price')) {
            return response()->json(['response' => 'bad request', 'required fields' => ['price']], 400);
        }

        // Get User per mail
        if (!$user = $this->userServices->getUserByEmail($request->input('email'))) {
            $user = $this->userServices->saveUser($request);
        }

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
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, false);

        $this->userServices->affectAccessElement($user->user_id, $accessNotInRegister);


        //TODO Manage Organization Acceptation


        //Save Access Premium
        if ($privilegeId == 3) {
            $this->userServices->affectAccess($user->user_id, $request->input('accessIds'), []);
        } else {
            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, true);
            $this->userServices->affectAccessElement($user->user_id, $accessInRegister);
        }


        //Free Inscription (By Chance)
        $congress = $this->congressServices->getCongressById($congress_id);
        $isFree = false;
        if ($privilegeId == 3) {
            $nbParticipants = $this->congressServices->getParticipantsCount($congress_id);
            $freeNb = $this->paymentServices->getFreeUserByCongressId($congress_id);
            if ($freeNb < $congress->config->free && !($nbParticipants % 10)) {
                $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $request->input("price"), true);
                $isFree = true;
            }
        }
        // Sending Mail
        $link = $request->root() . "/api/users/" . $user->user_id . '/congress/' . $congress_id . '/validate/' . $user->verification_code;
        $user = $this->userServices->getUserIdAndByCongressId($user->user_id, $congress_id, true);
        if ($privilegeId != 3 || !$congress->config->has_payment || $isFree) {
            //Free Mail
            if ($isFree) {
                if ($mailtype = $this->congressServices->getMailType('free')) {
                    if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false, $userMail);
                    }
                }
            }
            //Confirm Direct
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                $fileAttached = true;
            }
            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached, $userMail);
                }
            }
        } else {
            //PreInscription First (Payment Required)
            //Add Payement Ligne
            $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $request->input("price"), false);

            $user->price = $request->input("price");
            if ($mailtype = $this->congressServices->getMailType('inscription')) {
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, $link, null), $user, $congress, $mail->object, false, $userMail);
                }
            }
        }

        return $user;
    }

    public function editerUserToCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        if ($request->has("organization_id") && $request->input("organization_id") &&
            !$this->organizationServices->getOrganizationById($request->input("organization_id"))) {
            return response()->json(['error' => 'organization not found'], 404);
        }

        if (!$user = $this->userServices->getUserById($request->input('user_id'))) {
            return response()->json(['error' => 'user not found'], 400);
        }

        $accessIds = $request->input("accessIds");
        $request->merge(["congressId" => $congressId]);

        $user->price = $this->calculPrice($congress, $request->input('pack_id'), $accessIds);

        $user = $this->userServices->editerUser($request, $user);

        $this->userServices->deleteUserResponses($user->user_id);

        $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);

        $accessIdsIntutive = $this->accessServices->getIntuitiveAccessIds($congressId);
        $userAccessIds = $this->accessServices->getAccessIdsByAccess($user->accesss);
        if ($accessIds && array_count_values($accessIds)) {
            $accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));
            $accessDiffDeleted = array_diff($userAccessIds, $accessIds);
            $accessDiffAdded = array_diff($accessIds, $userAccessIds);
            $this->userServices->affectAccessIds($user->user_id, $accessDiffAdded);
            $this->userServices->deleteAccess($user->user_id, $accessDiffDeleted);
        } else if ($userAccessIds && array_count_values($userAccessIds)) $this->userServices->deleteAccess($user->user_id, $userAccessIds);
        $user = $this->userServices->getParticipatorById($user->user_id);

        return response()->json($user, 200);
    }

    function validateUserAccount($userId = null, $congressId = null, $token = null)
    {
        Log::info($token);
        $user = $this->userServices->getUserById($userId);
        if (!$user) {
            return response()->json(['response' => 'Votre compte à été supprimé'], 404);
        }
        if ($token == $user->verification_code) {
            $user->email_verified = 1;
            $user->update();

            return response()->redirectTo(Utils::baseUrlWEB . "/#/auth/user/" . $user->user_id . "/upload-payement?token=" . $token . "&congressId=" . $congressId);
        } else {
            return response()->json(['response' => 'Token not match'], 400);
        }
    }

    public function getUsersByAccess($accessId)
    {
        $users = $this->userServices->getUsersByAccess($accessId);

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

    public function editFastUserToCongress($congressId, $userId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }
        if ($request->has('email') && $request->input('email') != "") {
            $userMail = $this->userServices->getUserByEmail($congressId, $request->input('email'));
            if ($userMail && $userMail->user_id != $userId) {
                return response()->json(['error' => 'user exist'], 400);
            }
        }
        $accessIds = $request->input("accessIds");
        $request->merge(["congressId" => $congressId]);
        $user = $this->userServices->editFastUser($user, $request);
        $accessIdsIntutive = $this->accessServices->getIntuitiveAccessIds($congressId);
        $accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));
        //DENTAIRE DE MERDE
        if (in_array(8, $accessIds)) {
            array_push($accessIds, 25);
        }

        $userAccessIds = $this->accessServices->getAccessIdsByAccess($user->accesss);

        Log::info($userAccessIds);
        Log::info($accessIds);
        $accessDiffDeleted = array_diff($userAccessIds, $accessIds);
        $accessDiffAdded = array_diff($accessIds, $userAccessIds);


        $this->userServices->affectAccessIds($user->user_id, $accessDiffAdded);
        $this->userServices->deleteAccess($user->user_id, $accessDiffDeleted);
        $user = $this->userServices->getUserById($user->user_id);
        return response()->json($user, 200);


    }

    public function getAllPayementTypes()
    {
        return response()->json($this->paymentServices->getAllPaymentTypes());
    }

    public function addingFastUserToCongress($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        if ($request->has('email') && $request->input('email') != "") {
            if ($user = $this->userServices->getUserByEmail($congressId, $request->input('email'))) {
                return response()->json(['error' => 'user exist'], 400);
            }
        }
        $accessIds = $request->input("accessIds");
        $request->merge(["congressId" => $congressId]);
        $user = $this->userServices->addFastUser($request);
        $accessIdsIntutive = $this->accessServices->getIntuitiveAccessIds($congressId);
        $accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));
        //DENTAIRE DE MERDE
        if (in_array(8, $accessIds)) {
            array_push($accessIds, 25);
        }
        $this->userServices->affectAccess($user->user_id, $accessIds, $user->pack->accesses);
        $user = $this->userServices->getUserById($user->user_id);
        if ($request->has('email') && $request->input('email') != "") {
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                if ($congress->has_paiement) {
                    if ($mailtype = $this->congressServices->getMailType('paiement')) {
                        if ($mail = $this->congressServices->getMail($congressId, $mailtype->mail_type_id)) {
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false);
                        }
                    }
                } else {
                    $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
                    $fileAttached = false;
                    if ($badgeIdGenerator != null) {
                        $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                            ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                            $user->qr_code);
                        $fileAttached = true;
                    }
                    if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                        if ($mail = $this->congressServices->getMail($congressId, $mailtype->mail_type_id)) {
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached);
                        }
                    }
                }

            }
        }
        return response()->json($user, 201);
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
        if (!$user = $this->userServices->getUserById($userPayement->user_id)) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $congress = $this->congressServices->getCongressById($userPayement->congress_id);

        $userCongress = $this->userServices->getUserCongress($congress->congress_id, $user->user_id);

        if ($userPayement->isPaid == 2 && $isPaid == 1) {
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $userCongress->privilege_id);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                $fileAttached = true;
            }

            // $link = Utils::baseUrlWEB . "/#/auth/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
            if ($mailtype = $this->congressServices->getMailType('paiement')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, null, $userMail);
                }
            }

            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached, $userMail);
                }
            }

        }
        $userPayement->isPaid = $isPaid;
        $userPayement->update();

        return response()->json(['message' => 'user updated success']);
    }

    public function saveUsersFromExcel($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $users = $request->all();
        $this->userServices->saveUsersFromExcel($congress->congress_id, $users);
        return response()->json(['message' => 'add congress success']);
    }

    public function sendMailAttesation($userId)
    {

        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($user->congress_id);
        $request = array();
        if ($user->email != null && $user->email != "-" && $user->email != "") {
            if ($congress->attestation) {
                array_push($request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false
                    ));
            }
            foreach ($user->accesss as $access) {
                if ($access->pivot->isPresent == 1) {
                    if ($access->attestation) {
                        array_push($request,
                            array(
                                'badgeIdGenerator' => $access->attestation->attestation_generator_id,
                                'name' => Utils::getFullName($user->first_name, $user->last_name),
                                'qrCode' => false
                            ));
                    }

                }
            }
            $this->badgeServices->saveAttestationsInPublic($request);

            $mailtype = $this->congressServices->getMailType('attestation');
            if (!$mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id))
                return response()->json(['error' => 'attestation mail not sent']);

            $this->userServices->sendMailAttesationToUser($user, $congress, $mail->object, $this->congressServices->renderMail($mail->template, $congress, $user, null, null));
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

        $user = $this->userServices->uploadPayement($paymentUser, $request);

        if ($mailtype = $this->congressServices->getMailType('upload')) {
            if ($mail = $this->congressServices->getMail($paymentUser->congress_id, $mailtype->mail_type_id)) {
                $congress = $this->congressServices->getCongressById($paymentUser->congress_id);
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $paymentUser->user_id);
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false, $userMail);
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
        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false);
        return response()->json(['response' => 'success'], 200);

    }

    function userConnect($qrCode)
    {
        $user = $this->userServices->getUserByQrCode($qrCode);
        return $user ? response()->json($user, 200, []) : response()->json(["error" => "wrong qrcode"], 404);
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
        if (!$user = $this->userServices->getUserById($user_id))
            return response()->json(['error' => 'user not found'], 400);
        if ($this->userServices->usedQrCode($request->qrCode))
            return response()->json(['error' => 'used-qr-code'], 400);
        $user->qr_code = $request->get('qrcode');
        $user->save();
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
