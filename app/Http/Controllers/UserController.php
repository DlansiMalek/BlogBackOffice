<?php

namespace App\Http\Controllers;


use App\Models\AttestationRequest;
use App\Models\User;
use App\Models\UserCongress;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\OrganizationServices;
use App\Services\PackServices;
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
    protected $packServices;
    protected $organizationServices;

    function __construct(UserServices $userServices, CongressServices $congressServices,
                         AdminServices $adminServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         AccessServices $accessServices,
                         PackServices $packServices,
                         OrganizationServices $organizationServices)
    {
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->accessServices = $accessServices;
        $this->packServices = $packServices;
        $this->organizationServices = $organizationServices;
    }

    public function getUserByTypeAndCongressId($congress_id, $privilege_id)
    {
        return $this->userServices->getUserByTypeAndCongressId($congress_id, $privilege_id);
    }

    public function index()
    {
        return $this->userServices->getAllUsers();
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
        $this->userServices->affectAccess($user->user_id, $accessIds, $user->pack->accesses);

        return response()->json(['add success'], 200);
    }

    public function saveUser(Request $request, $congress_id, $strict_mode)
    {
        if (!$request->has('email'))
            return response()->json(['response' => 'bad request', 'required fields' => ['email']], 400);
        $user = $this->userServices->getUserById($request->input('email'));

        if ($user && !$request->has(['accesses', 'privilege_id']))
            return response()->json(['response' => 'bad request', 'required fields' => ['accesses', 'privilege_id']], 400);
        if (!$user && $strict_mode && !$request->has(['first_name', 'last_name', 'gender', 'mobile', 'country_id', 'accesses', 'privilege_id']))
            return response()->json(['response' => 'bad request', 'required fields' => ['first_name', 'last_name', 'gender', 'mobile', 'country_id', 'accesses', 'privilege_id']], 400);
        if (!$user && !$strict_mode && !$request->has(['first_name', 'last_name', 'accesses', 'privilege_id']))
            return response()->json(['response' => 'bad request', 'required fields' => ['first_name', 'last_name', 'accesses', 'privilege_id']], 400);

        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(['response' => 'congress not found'], 404);

        if (!$user) $user = new User();
        $user = $this->userServices->saveUser($request, $user);

        if (!$user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id))
            $user_congress = new UserCongress();
        $user_congress = $this->userServices->saveUserCongress($user_congress, $congress, $user, $request);
        $user->user_congress = $user_congress;
        $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);
        $this->userServices->deleteUserAccesses($user->user_id, $congress_id);
        if ($user->privilege_id == 3) {
            if ($user_congress->pack_id) {
                $pack = $this->packServices->getPackById($user->pack_id);
                $this->userServices->affectAccess($user->user_id, $request->input('accesses'), $pack->accesses);
            } else $this->userServices->affectAccess($user->user_id, $request->input('accesses'), []);
        } else {
            $accesss = $this->accessServices->getAllAccessByCongress($congress_id);
            $this->userServices->affectAllAccess($user->user_id, $accesss);
        }
        return $user;
        //TODO Mail sending is to be redone depending on the new design
        //TODO Manage organization cases (accepted...)
    }

    public function registerUserToCongress(Request $request, $congressId)
    {

        if ($request->has('organization_accepted') && $request->get('organization_accepted')) {
            if (!$request->has(['first_name', 'last_name', 'gender'])
            ) {
                return response()->json(['response' => 'invalid request',
                    'content' => ['first_name', 'last_name', 'mobile', 'email',
                        'price', 'gender', 'country_id', 'organization_id']], 400);
            }
        } else
            if (!$request->has(['first_name', 'last_name', 'mobile', 'email',
                'price', 'gender', 'country_id'])) {
                return response()->json(['response' => 'invalid request',
                    'content' => ['first_name', 'last_name', 'mobile', 'email',
                        'price', 'gender', 'country_id', 'organization_id']], 400);
            }

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        if ($user = $this->userServices->getUserByEmail($congressId, $request->input('email'))

//            ||$user = $this->userServices->getUserByNameAndFName($congressId, $request->input('first_name'),$request->input('last_name'))
        ) {
            return response()->json(['error' => 'user exist'], 400);
        }

        if ($request->has("organization_id") && $request->input("organization_id") &&
            !$this->organizationServices->getOrganizationById($request->input("organization_id"))) {
            return response()->json(['error' => 'organization not found'], 404);
        }

        if ((!$request->has('organization_accepted') || !$request->get('organization_accepted')) && $user = $this->userServices->getUserByEmail($congressId, $request->input('email'))) {
            return response()->json(['error' => 'user exist'], 400);
        }

        if ($user = $this->userServices->getUserByEmail($congressId, $request->input('email'))
            || $user = $this->userServices->getUserByNameAndFName($congressId, $request->input('first_name'), $request->input('last_name'))) {
            return response()->json(['error' => 'user exist'], 400);
        }

        $accessIds = $request->input("accessIds");

        $request->merge(["congressId" => $congressId]);
        $freeUsersCount = $this->userServices->getFreeCountByCongressId($congressId);
        $totalUsersCount = $this->userServices->getUsersCountByCongressId($congressId);
        if ((!$request->input('privilege_id') || $request->input('privilege_id') == 3) && $freeUsersCount < $congress->free && !($totalUsersCount % 10))
            $request->merge(["free" => 1]);
        $user = $this->userServices->registerUser($request);


        $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);

        $accessIdsIntutive = $this->accessServices->getIntuitiveAccessIds($congressId);

        $accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));

        if ($user->privilege_id == 3) {
            if ($user->pack)
                $this->userServices->affectAccess($user->user_id, $accessIds, $user->pack->accesses);
            else $this->userServices->affectAccess($user->user_id, $accessIds, []);
        } else {
            $accesss = $this->accessServices->getAllAccessByCongress($congressId);
            $this->userServices->affectAllAccess($user->user_id, $accesss);
        }

        if (!$user) {
            return response()->json(['response' => 'user exist'], 400);
        }
        $user = $this->userServices->getUserById($user->user_id);

        $link = $request->root() . "/api/users/" . $user->user_id . '/validate/' . $user->verification_code;
        if ($user->privilege_id == 3) {
            if ($request->has('organization_accepted') && $request->get('organization_accepted')) {
                $organization = $this->organizationServices->getOrganizationById($user->organization_id);
                $organization->congress_organization->montant += $user->price;
                $organization->congress_organization->update();
                if ($user->email) {
                    $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
                    $fileAttached = false;
                    if ($badgeIdGenerator != null) {
                        $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                            ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                            $user->qr_code);
                        $fileAttached = true;
                    }

                    $link = Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
                    if ($mailtype = $this->congressServices->getMailType('subvention')) {
                        if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, null,
                                $link);
                        }
                    }

                    if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                        if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached,
                                $link);
                        }
                    }

                }
            } else if ($congress->has_paiement) {

                if ($user->organization_accepted) {
                    if ($mailtype = $this->congressServices->getMailType('free')) {
                        if ($mail = $this->congressServices->getMail($congressId, $mailtype->mail_type_id)) {
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false,
                                null);
                        }
                    }

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
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached,
                                null);
                        }
                    }
                } else {
                    if ($mailtype = $this->congressServices->getMailType('inscription')) {
                        if ($mail = $this->congressServices->getMail($congressId, $mailtype->mail_type_id)) {
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, $link, null), $user, $congress, $mail->object, false,
                                $link);
                        }
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
                        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached,
                            null);
                    }
                }
            }
        }


        return response()->json($this->userServices->getParticipatorById($user->user_id), 201);
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

    function validateUserAccount($student_id = null, $token = null)
    {
        $user = $this->userServices->getUserById($student_id);
        if (!$user) {
            return response()->json(['response' => 'Votre compte à été supprimé'], 404);
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
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false,
                                null);
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
                            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached,
                                null);
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

            $link = Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
            if ($mailtype = $this->congressServices->getMailType('paiement')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, null,
                        $link);
                }
            }

            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, $fileAttached,
                        $link);
                }
            }

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

    public function uploadPayement($userId, Request $request)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $user = $this->userServices->uploadPayement($user, $request);

        if ($mailtype = $this->congressServices->getMailType('upload')) {
            if ($mail = $this->congressServices->getMail($user->congress_id, $mailtype->mail_type_id)) {
                $congress = $this->congressServices->getCongressById($user->congress_id);
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false,
                    null);
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
        $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false,
            null);
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
        $user->isPresent = 1;
        $user->qr_code = $request->get('qrcode');
        $user->save();
        return $user;
    }
}
