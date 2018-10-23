<?php

namespace App\Http\Controllers;


use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    protected $userServices;
    protected $congressServices;
    protected $adminServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $accessServices;

    function __construct(UserServices $userServices, CongressServices $congressServices,
                         AdminServices $adminServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         AccessServices $accessServices)
    {
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->accessServices = $accessServices;
    }

    public function index()
    {
        return $this->userServices->getAllUsers();
    }

    public function register(Request $request)
    {
        if (!$request->has(['first_name', 'last_name', 'mobile', 'email', 'congressId'])
        ) {
            return response()->json(['response' => 'invalid request',
                'content' => ['first_name', 'last_name', 'mobile', 'email', 'congressId']], 400);
        }
        $createdUser = $this->userServices->registerUser($request);
        if (!$createdUser) {
            return response()->json(['response' => 'user exist'], 400);
        }

        //Utils::generateQRcode($createdUser->qr_code);
        //$this->userServices->impressionBadge($createdUser);
        //$congress = $this->congressServices->getCongressById($request->input("congressId"));
        //$this->userServices->sendMail($createdUser, $congress);


        return response()->json($createdUser, 201);
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

    public function delete($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        $user->delete();
        return response()->json(['response' => 'user deleted'], 202);
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


    public function sendingMailWithAttachement($userId)
    {
        if (!$user = $this->userServices->getParticipatorById($userId)) {
            return response()->json(["error" => "User not found"], 404);
        }

        $this->userServices->impressionBadge($user);

        $this->userServices->sendMail($user);

        return response()->json(["message" => "email sending success"], 200);
    }

    public function getUsersByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersByCongress($congressId);

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
        $this->userServices->affectAccess($user->user_id, $accessIds);

        return response()->json(['add success'], 200);
        /*$file = new Filesystem();

        Utils::generateQRcode($user->qr_code, "qrcode.png");


        if ($file->exists(public_path() . "/qrcode.png")) {
            return response()->download(public_path() . "/qrcode.png")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }*/
    }

    public function registerUserToCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        if ($user = $this->userServices->getUserByEmail($congressId, $request->input('email'))) {
            return response()->json(['error' => 'user exist'], 400);
        }
        $accessIds = $request->input("accessIds");

        $request->merge(["congressId" => $congressId]);
        $user = $this->userServices->registerUser($request);

        $accessIdsIntutive = $this->accessServices->getIntuitiveAccessIds($congressId);

        $accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));

        $this->userServices->affectAccess($user->user_id, $accessIds);

        if (!$user) {
            return response()->json(['response' => 'user exist'], 400);
        }
        $user = $this->userServices->getUserById($user->user_id);

        if ($user->price != null) {
            $link = $request->root() . "/api/users/" . $user->user_id . '/validate/' . $user->verification_code;
            $this->userServices->sendMail("inscriptionEmail", $user, $congress, $congress->object_mail_inscription, false,
                $link);
        } else {
            $user->isPaied = 1;
            $user->upadate();
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                $fileAttached = true;
            }

            $link = Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/change-access?token=" . $user->verification_code;
            $this->userServices->sendMail("confirmPayement",
                $user,
                $congress,
                $congress->object_mail_payement,
                $fileAttached,
                $link);
        }

        return response()->json($user, 201);
    }

    function validateUserAccount($student_id = null, $token = null)
    {
        $user = $this->userServices->getUserById($student_id);
        if (!$user) {
            return response()->json(['response' => 'student not found'], 404);
        }
        if ($token == $user->verification_code) {
            $user->email_verified = 1;
            $user->update();

            return response()->redirectTo(Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/upload-payement?token=" . $token);
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

    public function getAllPayementTypes()
    {
        return response()->json($this->userServices->getAllPayementTypes());
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
        $this->userServices->affectAccess($user->user_id, $accessIds);
        $user = $this->userServices->getUserById($user->user_id);
        if ($request->has('email') && $request->input('email') != "") {
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                $this->userServices->sendMail("inscriptionEmail", $user, $congress, $congress->object_mail_inscription);
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

    public function changePaiement($userId, Request $request)
    {
        $isPaied = $request->input('status');

        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $congress = $this->congressServices->getCongressById($user->congress_id);

        if ($user->isPaied == 2 && $isPaied == 1) {
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                $fileAttached = true;
            }

            $link = Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/change-access?token=" . $user->verification_code;
            $this->userServices->sendMail("confirmPayement",
                $user,
                $congress,
                $congress->object_mail_payement,
                $fileAttached,
                $link);
        }
        $user->isPaied = $isPaied;
        $user->update();

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
        if ($user->email != null && $user->email != "-" && $user->email != "" && $user->isPresent == 1) {
            array_push($request,
                array(
                    'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                    'qrCode' => false
                ));
            foreach ($user->accesss as $access) {
                if ($access->pivot->isPresent == 1) {
                    $infoPresence = $this->badgeServices->getAttestationEnabled($user->user_id, $access);
                    if ($infoPresence['enabled'] == 1) {
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
            $this->userServices->sendMailAttesationToUser($user, $congress);
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }
        if ($user->email_attestation_sended == 1) {
            return response()->json(['message' => 'email sended success']);
        } else {
            return response()->json(['error' => 'cannot send mail', 'email' => $user->emaill], 501);
        }
    }

    public function uploadPayement($userId, Request $request)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $user = $this->userServices->uploadPayement($user, $request);

        return response()->json($user);
    }


}
