<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OffreServices;
use App\Services\PrivilegeServices;
use App\Services\SharedServices;
use App\Services\SubmissionServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected $userServices;
    protected $adminServices;
    protected $congressService;
    protected $privilegeServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $accessServices;
    protected $mailServices;
    protected $submissionServices;
    protected $client;
    protected $offreServices;

    public function __construct(
        UserServices $userServices,
        AdminServices $adminServices,
        CongressServices $congressService,
        PrivilegeServices $privilegeServices,
        SharedServices $sharedServices,
        BadgeServices $badgeServices,
        AccessServices $accessServices,
        SubmissionServices $submissionServices,
        MailServices $mailServices,
        OffreServices $offreServices
    ) {
        $this->userServices = $userServices;
        $this->adminServices = $adminServices;
        $this->congressService = $congressService;
        $this->privilegeServices = $privilegeServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->accessServices = $accessServices;
        $this->submissionServices = $submissionServices;
        $this->mailServices = $mailServices;
        $this->client = new Client();
        $this->offreServices = $offreServices;
    }


    /**
     * @SWG\Post(
     *   path="/mobile/scan/participant",
     *   summary="Scan Participant",
     *   tags={"Mobile"},
     *   operationId="scanParticipatorQrCode",
     *   security={
     *     {"Bearer": {}}
     *   },
     *   @SWG\Parameter(
     *     name="QrCode",
     *     in="query",
     *     description="QR code",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function scanParticipatorQrCode(Request $request)
    {
        if (!$request->has(['qrcode'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['qrcode', 'congressId']], 400);
        }

        $participator = $this->userServices->getParticipatorByQrCode($request->input('qrcode'), $request->input('congressId'));
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }
        if ($request->has(['congressId'])) {
            $userCongress = $this->userServices->getUserCongress($request->input('congressId'), $participator->user_id);
            $participator->isPresent = $userCongress->isPresent;
        } else {
            $participator->isPresent = 0;
        }
        foreach ($participator->accesses as $accesss) {
            if ($accesss->pivot->isPresent == 1) {
                $infoPresence = $this->badgeServices->getAttestationEnabled($participator->user_id, $accesss);
                $accesss->attestation_status = $infoPresence['enabled'];
                $accesss->time_in_access = $infoPresence['time'];
            } else {
                $accesss->attestation_status = 0;
            }
        }

        /*else if ($participator->email_verified == 0) {
            return response()->json(['resposne' => 'user not verified'], 404);
        }*/
        return response()->json($participator);
    }


    /**
     * @SWG\Post(
     *        path="/mobile/presence/{participantId}/status/update",
     *        tags={"Mobile"},
     *        operationId="makeUserPresentCongress",
     *        summary="makeUserPresentCongress",
     *        security={
     *          {"Bearer": {}}
     *        },
     * 		@SWG\Parameter(
     *            name="participantId",
     *            in="path",
     *            required=true,
     *            type="integer",
     *            description="participantId",
     *        ),
     *      @SWG\Parameter(
     *        name="isPresent",
     *        in="query",
     *        description="isPresent",
     *        required=true,
     *        type="integer"
     *      ),
     *      @SWG\Parameter(
     *        name="congressId",
     *        in="query",
     *        description="L'id du congrès",
     *        required=true,
     *        type="integer"
     *      ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public
    function makeUserPresentCongress(Request $request, $userId)
    {
        if (!$request->has(['isPresent', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['isPresent', 'congressId']], 400);
        }
        $participator = $this->userServices->getUserById($userId);
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }

        if ($participator->congress_id != $request->input("congressId")) {
            return response()->json(['response' => 'participator not participated in this congress'], 404);
        }
        $this->userServices->makePresentToCongress($participator, $request->input('isPresent'));
        return response()->json(["message" => "success sending and scaning"], 200);
    }

    /**
     * @SWG\Post(
     *        path="/mobile/presence/{participantId}/status/update/access",
     *        tags={"Mobile"},
     *        operationId="makeUserPresentAccess",
     *        summary="makeUserPresentAccess",
     *        security={
     *          {"Bearer": {}}
     *        },
     * 		@SWG\Parameter(
     *            name="participantId",
     *            in="path",
     *            required=true,
     *            type="integer",
     *            description="participantId",
     *        ),
     *      @SWG\Parameter(
     *        name="isPresent",
     *        in="query",
     *        description="isPresent",
     *        required=true,
     *        type="integer"
     *      ),
     *      @SWG\Parameter(
     *        name="accessId",
     *        in="query",
     *        description="L'id de l'accées",
     *        required=true,
     *        type="integer"
     *      ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function makeUserPresentAccess(Request $request, $userId = null)
    {
        //type : 1 : Enter Or 0 : Leave
        if (!$request->has(['isPresent', 'type', 'congressId'])) {
            return response()->json([
                'resposne' => 'bad request',
                'required fields' => ['isPresent', 'type', 'congressId']
            ], 400);
        }
        $congressId = $request->input('congressId');

        if (!$userId) {
            $user = $this->userServices->retrieveUserFromToken();
            $userId = $user->user_id;
        }

        $participator = $this->userServices->getUserByIdWithRelations(
            $userId,
            ['user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }]
        );

        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }

        if (sizeof($participator->user_congresses) == 0 || !$participator->user_congresses[0]) {
            return response()->json(['response' => 'participator not inscrit in congress']);
        }
        /* Make it present in congress */
        $userCongress = $participator->user_congresses[0];
        $userCongress->isPresent = 1;
        $userCongress->update();

        $accessId = $request->input("accessId");

        if ($request->has('channel_name')) {
            $access = $this->accessServices->getAccessByName($request->input('channel_name'));
            if ($access)
                $accessId = $access->access_id;
        }

        if (!$accessId) {
            return response()->json(['resposne' => 'scan congress presence success'], 200);
        }

        $user_access = $this->userServices->getUserAccessByUser($participator->user_id, $accessId);

        if (!$user_access) {
            return response()->json(["message" => "user not allowed to this access"], 401);
        }

        if ($user_access->isPresent == 0 && $request->input('type') == 0) {
            return response()->json(['message' => 'cannot leave , enter first'], 401);
        }

        $this->userServices->makePresentToAccess(
            $user_access,
            $participator,
            $accessId,
            $request->input('isPresent'),
            $request->input('type')
        );

        return response()->json(["message" => "success sending and scanning"], 200);
    }

    public function makeUserPresent(Request $request, $userId)
    {
        if (!$request->has(['isPresent', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['isPresent', 'accessId']], 400);
        }
        $congressId = $request->input("congressId");

        $participator = $this->userServices->getUserByIdWithRelations(
            $userId,
            ['user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }]
        );
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }

        /* Make it present in congress */
        if (sizeof($participator->user_congresses) == 0 || !$participator->user_congresses[0]) {
            return response()->json(['response' => 'participator not inscrit in congress']);
        }
        /* Make it present in congress */
        $userCongress = $participator->user_congresses[0];
        $userCongress->isPresent = 1;
        $userCongress->update();

        return response()->json(["message" => "success scanning"], 200);
    }

    public function getAuhenticatedAdmin()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $admin = $this->adminServices->getAdminById($admin->admin_id);

        return response()->json(['admin' => $admin]);
    }

    public function getAdminWithCurrentCongressFirst($congress_id)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $admin = $this->adminServices->getAdminWithCurrentCongressFirst($admin->admin_id, $congress_id);
        if ($admin->privilege_id == 1) {
            $menus = $this->getAdminMenus($admin->admin_id);
        } else {
            if (count($admin->admin_congresses) > 0) {
                $menus = $this->offreServices->getMenusByPrivilegeByCongress($admin->admin_congresses[0]->congress_id, $admin->admin_congresses[0]->privilege_id);
                if (count($menus) == 0) {
                    $admin_congress = $this->adminServices->getAdminOfCongress($congress_id);
                    $menus = $this->getAdminMenus($admin_congress->admin_id);
                }
            }
        }
        return response()->json(['admin' => $admin, 'menus' => $menus]);
    }

    public function getAdminMenus($admin_id)
    {
        $offre = $this->offreServices->getActiveOffreByAdminId($admin_id);
        if (!$offre) {
            $menus = $this->offreServices->getAllMenu();
        } else {
            $menus = $this->offreServices->getMenusByOffre($offre->offre_id);
            if (count($menus) == 0) {
                $menus = $this->offreServices->getAllMenu();
            }
        }
        return $menus;
    }

    public function getAdminCongresses()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        return $this->adminServices->getAdminCongresses($admin);
    }

    public
    function getAllParticipantsByCongress($congressId)
    {
        $participants = $this->userServices->getAllParticipatorByCongress($congressId);

        return response()->json($participants, 200);
    }

    public
    function getAllPresenceByCongress($congressId)
    {
        $presences = $this->userServices->getAllPresentParticipatorByCongress($congressId);

        return response()->json($presences, 200);
    }

    public
    function cleanBadges()
    {
        File::cleanDirectory(public_path() . '/badge/jnn');
        return response()->json(["message" => "Badges deleted"]);
    }

    /**
     * @SWG\Get(
     *   path="/admin/me/personels/list",
     *   summary="Get personels by Admin",
     *   operationId="getListPersonels",
     *   security={
     *     {"Bearer": {}}
     *   },
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function getListPersonels($congress_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $personels = $this->adminServices->getListPersonelsByAdmin($congress_id);

        return response()->json($personels);
    }

    public function getAdminsByPrivilege($congress_id, $privilege_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $personels = $this->adminServices->getOrganismAdmins($congress_id);

        return response()->json($personels);
    }

    public function addPersonnel(Request $request, $congress_id)
    {

        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $admin = $request->input('admin');
        $privilegeId = (int)$request->input('privilege_id');
        $password = Str::random(8);
        // if exists then update or create admin in DB
        if (!($fetched = $this->adminServices->getAdminByLogin($admin['email']))) {
            $admin    = $this->adminServices->addPersonnel($admin, $password);
            $admin_id = $admin->admin_id;
        } else {
            $admin_id = $fetched->admin_id;
            // check if he has already privilege to congress
            $admin_congress = $this->privilegeServices->checkIfAdminOfCongress($admin_id, $congress_id);
            if ($admin_congress) {
                return response()->json(['error' => 'Organisateur existant'], 505);
            }
            // else edit changed infos while creating
            $admin['admin_id'] = $admin_id;
            $this->adminServices->editPersonnel($admin);
        }

        $congress = $this->congressService->getById($congress_id);

        // Add User if not exist
        if (!$user = $this->userServices->getUserByEmail($admin['email'])) {
            $name = explode(" ", $admin['name']);
            $admin['first_name'] = isset($name[0]) ? $name[0] : '-';
            $admin['last_name']  = isset($name[1]) ? $name[1] : '-';
            $user = $this->userServices->addUserFromExcel($admin, $password);
            $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, null, null);
        } else {
            // Add user to congress if not affected
            if (!$user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
                $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, null, null);
            }
        }

        //create themeAdmin if privilege is "comité Scientifique"

        if ($privilegeId == 11) {
            $this->adminServices->affectThemesToAdmin($request->input("themesSelected"), $admin_id);
            $submissions = $this->submissionServices->getSubmissionsByCongressId($congress_id);
            if (sizeof($submissions) > 0) {
                $this->adminServices->affectEvaluatorToSubmissions(
                    $submissions,
                    $admin_id,
                    $request->input("themesSelected"),
                    $congress_id
                );
            }
        }
        $evalutors = $this->adminServices->getEvaluatorsByCongress($congress_id, 13, 'evaluations');
        if (
            $privilegeId == 13 &&
            $congress->config_selection && ($congress->congress_type_id == 2 || $congress->congress_type_id == 1) &&
            sizeof($evalutors) < $congress->config_selection->num_evaluators
        ) {

            $this->adminServices->affectUsersToEvaluator(
                $congress->users,
                $congress->config_selection->num_evaluators,
                $admin_id,
                $congress_id
            );
        }

        //create admin congress bind privilege admin and congress
        $admin_congress = $this->privilegeServices->affectPrivilegeToAdmin(
            $privilegeId,
            $admin_id,
            $congress_id
        );

        $admin = $this->adminServices->getAdminById($admin_id);
        if ($mailtype = $this->congressService->getMailType('organizer_creation')) {
            if (!$mail = $this->congressService->getMail($congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme Eventizer";
            }

            $badge = $this->congressService->getBadgeByPrivilegeId($congress, $privilegeId);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic(
                    $badge,
                    $admin,
                    $admin->passwordDecrypt,
                    $privilegeId
                );
            }
            $mail->template = $mail->template . "<br>Votre Email pour accéder à la plateforme <a href='https://organizer.eventizer.io'>Eventizer</a>: " . $admin->email;
            $mail->template = $mail->template . "<br>Votre mot de passe pour accéder à la plateforme <a href='https://organizer.eventizer.io'>Eventizer</a>: " . $admin->passwordDecrypt;

            $this->adminServices->sendMail($this->congressService->renderMail($mail->template, $congress, null, null, null, null), $congress, $mail->object, $admin, $fileAttached);
        }

        return response()->json($admin_congress);
    }

    public function editPersonels(Request $request, $congress_id, $admin_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $admin = $request->input('admin');
        $privilegeId = (int)$request->input('privilege_id');
        $this->adminServices->editPersonnel($admin);
        $this->privilegeServices->editPrivilege(
            $privilegeId,
            $admin_id,
            $congress_id
        );
        $newAdmin = $this->adminServices->getAdminById($admin_id);
        //message d'erreur à revoir
        $user = $this->userServices->getUserByEmail($admin['email']);
        $name = explode(" ", $admin['name']);
        $admin['first_name'] = $name[0];
        $admin['last_name'] = $name[1];
        if (!$user) {
            $user = $this->userServices->addUserFromExcel($admin, $newAdmin->passwordDecrypt);
            $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, null, null);
        } else {
            $this->userServices->editUserData($user, $admin);
            $user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id);
            $this->userServices->editUserPrivilege($user_congress, $privilegeId);
        }

        if ($privilegeId == 11) {
            $themesAdmin = $this->adminServices->getThemeAdmin($admin['admin_id']);
            $this->adminServices->modifyAdminThemes($themesAdmin, $admin['admin_id'], $request->input('themesSelected'));
        }
        return response()->json(['message' => 'working'], 200);
    }

    public
    function deletePersonnel($congress_id, $admin_id)
    {
        if (!$admincongress = $this->privilegeServices->checkIfAdminOfCongress($admin_id, $congress_id)) {
            return response()->json(["message" => "admin not found"], 404);
        }
        $this->privilegeServices->deleteAdminCongressByIds($admincongress);
        return response()->json(["message" => "deleted success"]);
    }

    public function getPersonelByIdAndCongressId($congress_id, $admin_id)
    {
        if (!$admincongress = $this->privilegeServices->checkIfAdminOfCongress($admin_id, $congress_id)) {
            return response()->json(["message" => "admin not found"], 404);
        }
        $result = $this->adminServices->getPersonelsByIdAndCongressId($congress_id, $admin_id);
        return response()->json($result);
    }

    public
    function downloadQrCode($adminId)
    {
        if (!$admin = $this->adminServices->getAdminById($adminId)) {
            return response()->json(["error" => "admin not found"]);
        }

        $file = new Filesystem();

        Utils::generateQRcode($admin->passwordDecrypt, "qrcode.png");

        if ($file->exists(public_path() . "/qrcode.png")) {
            return response()->download(public_path() . "/qrcode.png")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }
    }

    public function sendCredentialsViaEmailToOrganizer($adminId, Request $request)
    {

        $congressId = $request->input('congressId');

        if (!$congress = $this->congressService->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }


        if (!$admin = $this->adminServices->getAdminById($adminId)) {
            return response()->json(["error" => "admin not found"]);
        }


        $admin_congress = $this->privilegeServices->checkIfAdminOfCongress(
            $adminId,
            $congressId
        );

        if ($mailtype = $this->congressService->getMailType('organizer_creation')) {
            if (!$mail = $this->congressService->getMail($congressId, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme Eventizer";
            }

            $badge = $this->congressService->getBadgeByPrivilegeId($congress, $admin_congress->privilege_id);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic(
                    $badge,
                    $admin,
                    $admin->passwordDecrypt,
                    $admin_congress->privilege_id
                );
            }
            $mail->template = $mail->template . "<br>Votre Email pour accéder à la plateforme <a href='https://eventizer.vayetek.com'>Eventizer</a>: " . $admin->email;
            $mail->template = $mail->template . "<br>Votre mot de passe pour accéder à la plateforme <a href='https://eventizer.vayetek.com'>Eventizer</a>: " . $admin->passwordDecrypt;

            $this->adminServices->sendMail($this->congressService->renderMail($mail->template, $congress, null, null, null, null), $congress, $mail->object, $admin, $fileAttached);
        }
        return response()->json(['message' => 'sending credentials mails']);
    }

    function updateUserRfid(request $request, $userId)
    {
        if (!$request->has(['rfid'])) {
            return response()->json(['error' => 'bad request'], 400);
        }

        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $rfid = $request->input('rfid');
        if ($userExistsWithRfid = $this->userServices->getUserByRfid($rfid)) {
            $userExistsWithRfid->rfid = null;
            $userExistsWithRfid->update();
        }
        $user->rfid = $rfid;
        $user->update();
        return response()->json(['error' => 'user rfid updated'], 200);
    }

    function getAttestationByUserRfid(request $request)
    {
        if (!$request->has(['rfid'])) {
            return response()->json(['error' => 'bas request'], 400);
        }
        if (!$user = $this->userServices->getUserByRfid($request->input('rfid'))) {
            return response()->json(['error' => 'user not found'], 404);
        }
        return $user;
    }

    function getFastUsersByCongress($congressId)
    {
        return response()->json($this->userServices->getFastUsersByCongressId($congressId));
    }

    function setRefPayment($userId, Request $request)
    {
        $reference = $request->input('reference');
        $congressId = $request->input('congressId');

        if (!$userPayment = $this->userServices->getPaymentByUserId($congressId, $userId)) {
            return response()->json(['error' => 'user not found']);
        }

        $userPayment->reference = $reference;
        $userPayment->update();

        $user = $userPayment->user;

        if ($user->email && $user->mobile && $user->first_name && $user->last_name) {
            $client = new Client();
            $res = $client->request('POST', UrlUtils::getUrlPaiement() . '/api/payment/user/set-refpayement', [
                'json' => [
                    'user' => [
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'name' => $user->first_name . " " . $user->last_name
                    ],
                    'price' => $userPayment->price,
                    'reference' => $userPayment->reference,
                    'url' => UrlUtils::getBaseImgUrl()
                ]
            ]);
        }

        return response()->json(["reference" => $userPayment->reference]);
    }

    // getting only admins with privilege = 1
    public function getClients()
    {
        return $this->adminServices->getClients();
    }

    public function addClient(Request $request)
    {
        if (!$request->has(['name', 'email', 'passwordDecrypt', 'mobile']))
            return response()->json(['message' => 'bad request'], 400);

        if ($admin = $this->adminServices->getAdminByLogin($request->input("email"))) {
            if ($admin->privilege_id)
                return response()->json(['message' => 'admin exists'], 400);
        }

        if (!$mailTypeAdmin = $this->mailServices->getMailTypeAdmin('creation_admin')) {
            return response()->json(['message' => 'Mail type not found'], 400);
        }

        $mailAdmin = $this->mailServices->getMailAdmin($mailTypeAdmin->mail_type_admin_id);

        if (!$mailAdmin) {
            return response()->json(['message' => 'Mail not found'], 400);
        }

        $admin = $this->adminServices->addClient($admin, $request);

        $linkBackOffice = UrlUtils::getUrlEventizerWeb();

        $this->adminServices->sendMAil($this->adminServices->renderMail($mailAdmin->template, $admin, null, null, $linkBackOffice), null, $mailAdmin->object, $admin, null, null);

        return response()->json(['message' => 'Client added success', 'admin' => $admin]);
    }

    public function getClientById($admin_id)
    {
        if (!$admin = $this->adminServices->getClientById($admin_id)) {
            return response()->json(["error" => "client not found"], 404);
        }
        return response()->json($admin);
    }

    public function editClient(Request $request, $clientId)
    {
        if (!$request->has(['name', 'email', 'mobile', 'passwordDecrypt']))
            return response()->json(['message' => 'bad request'], 400);
        if (!$updatedAdmin = $this->adminServices->getClientById($clientId)) {
            return response()->json(["message" => "client not found"], 404);
        }

        $admin = $this->adminServices->editClient($request, $updatedAdmin);

        return response()->json($admin);
    }

    public function editClientPayment(Request $request, $clientId, $offreId)
    {
        if (!$admin = $this->adminServices->getAdminById($clientId)) {
            return response()->json(['messsage' => 'no admin found'], 404);
        }
        if (!$adminPayment = $this->adminServices->getAdminPayment($clientId, $offreId)) { {
                return response()->json(['messsage' => 'no admin payment found'], 404);
            }
        }
        if (!$request->has('isPaid')) {
            return response()->json(['error' => 'Bad request'], 400);
        }
        $isPaid = $request->input('isPaid');
        $adminPayment = $this->adminServices->editAdminPayment($adminPayment, $isPaid);
        return response()->json(['payment_admin' => $adminPayment], 200);
    }
}
