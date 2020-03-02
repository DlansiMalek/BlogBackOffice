<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\HistoryPack;
use App\Models\Mail;
use App\Models\PaymentAdmin;
use App\Models\User;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\PackAdminServices;
use App\Services\PrivilegeServices;
use App\Services\SharedServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Zipper;

class AdminController extends Controller
{
    protected $userServices;
    protected $adminServices;
    protected $congressService;
    protected $privilegeServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $packAdminServices;
    protected $accessServices;

    protected $client;

    public function __construct(UserServices $userServices,
                                AdminServices $adminServices,
                                CongressServices $congressService,
                                PrivilegeServices $privilegeServices,
                                SharedServices $sharedServices,
                                PackAdminServices $packAdminServices,
                                BadgeServices $badgeServices,
                                AccessServices $accessServices)
    {
        $this->userServices = $userServices;
        $this->adminServices = $adminServices;
        $this->congressService = $congressService;
        $this->privilegeServices = $privilegeServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->packAdminServices = $packAdminServices;
        $this->accessServices = $accessServices;
        $this->client = new Client();
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
    public function makeUserPresentAccess(Request $request, $userId)
    {
        //type : 1 : Enter Or 0 : Leave
        if (!$request->has(['isPresent', 'accessId', 'type', 'congressId'])) {
            return response()->json(['resposne' => 'bad request',
                'required fields' => ['isPresent', 'accessId', 'type', 'congressId']], 400);
        }
        $congressId = $request->input('congressId');
        $accessId = $request->input("accessId");

        $participator = $this->userServices->getUserByIdWithRelations($userId,
            ['user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }]);

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

        $access = $this->accessServices->getAccessById($accessId);

        $user_access = $this->userServices->getUserAccessByUser($participator->user_id, $accessId);
        if ($access->price && !$user_access) {
            return response()->json(["message" => "user not allowed to this access"], 401);
        }

        if (!$user_access) {
            $user_access = $this->userServices->affectAccessById($userId, $accessId);
        }

        if ($user_access->isPresent == 0 && $request->input('type') == 0) {
            return response()->json(['message' => 'cannot leave , enter first'], 401);
        }

        $this->userServices->makePresentToAccess($user_access, $participator,
            $request->input('accessId'), $request->input('isPresent'), $request->input('type'));

        return response()->json(["message" => "success sending and scanning"], 200);
    }

    public function makeUserPresent(Request $request, $userId)
    {
        if (!$request->has(['isPresent', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['isPresent', 'accessId']], 400);
        }
        $congressId = $request->input("congressId");

        $participator = $this->userServices->getUserByIdWithRelations($userId,
            ['user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }]);
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

        return response()->json(compact('admin'));
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
    function updateUserWithCongress()
    {
        set_time_limit(3600);

        $users = User::where("id_User", ">", "970")
            ->get();
        foreach ($users as $user) {
            $userCongress = Congress_User::where('id_User', '=', $user->id_User)->first();
            if (is_null($userCongress)) {
                Congress_User::create([
                    'id_User' => $user->id_User,
                    'id_Congress' => 4
                ])->save();
            }
        }
        return response()->json(['response' => 'all user congresses updated'], 200);
    }


    public
    function updateUsers()
    {
        $users = Inscription_Neuro2018::where("id_inscription", ">", "129")->get();
        foreach ($users as $user) {
            $userNew = User::create([
                'first_name' => $user->prenom,
                'last_name' => $user->nom,
                'profession' => $user->status,
                'email' => $user->email,
                'address' => $user->adresse,
                'mobile' => $user->tel,
                'transport' => $user->transport,
                'repas' => $user->repas,
                'diner' => $user->diner,
                'hebergement' => $user->hebergement,
                'chambre' => $user->chambre,
                'conjoint' => $user->conjoint,
                'date_arrivee' => $user->date_arrivee,
                'date_depart' => $user->date_depart,
                'date' => $user->date,
                'qr_code' => $user->qr_code
            ])->save();
        }
        return response()->json(['response' => 'all users updated'], 200);
    }

    public
    function generateUserQrCode()
    {
        set_time_limit(3600);
        $users = User::all();
        foreach ($users as $user) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $user->qr_code = $randomString;
            $user->update();
        }
    }

    public
    function generateBadges($userPos)
    {
        ini_set("memory_limit", "-1");
        set_time_limit(3600);

        $users = $this->userServices->getUsersByCongress(4, $userPos);
        File::cleanDirectory(public_path() . '/badge/neuro');
        for ($i = 0; $i < sizeof($users) / 3; $i++) {
            $tempUsers = array_slice($users, $i * 3, 3);
            $j = 1;
            $pdfFileName = '';
            foreach ($tempUsers as $tempUser) {
                Utils::generateQRcode($tempUser['qr_code'], 'qrcode_' . $j);
                $pdfFileName .= '_' . $tempUser['id_User'];
                $j++;
            }
            $data = [
                'users' => json_decode(json_encode($tempUsers), false)];

            $pdf = PDF::loadView('pdf.badges-09-03', $data);
            //return $pdf->stream('badges-09-03.pdf');
            $pdf->save(public_path() . '/badge/neuro/badges' . $pdfFileName . '.pdf');
        }
        $files = glob(public_path() . '/badge/neuro/*');
        Zipper::make(public_path() . '/badge/neuro/neuro_badges.zip')->add($files)->close();
        return response()->download(public_path() . '/badge/neuro/neuro_badges.zip');
        //return $pdf->stream('badges.pdf');
    }

    public
    function cleanBadges()
    {
        File::cleanDirectory(public_path() . '/badge/jnn');
        return response()->json(["message" => "Badges deleted"]);
    }

    /*
    public
    function updatePaiedParticipator($userId, Request $request)
    {
        if (!$request->has(['status', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['status', 'congressId']], 400);
        }
        if (!$congressUser = $this->adminServices->updateStatusPaied($userId, $request->input("status"), $request->input("congressId"))) {
            return response()->json(["error" => "User not inscrit Congress"]);
        }

        return response()->json(["message" => "status update success"]);

    }
    */

    public
    function generateTickets()
    {
        set_time_limit(3600);
        for ($i = 231; $i <= 400; $i++) {
            User::create([
                "first_name" => "Ticket",
                "last_name" => $i,
            ])->save();
        }
        for ($i = 1; $i <= 100; $i++) {
            User::create([
                "first_name" => "Invitation",
                "last_name" => $i,
            ])->save();
        }
        return response()->json(['response' => 'tickets registred'], 200);
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
    public
    function getListPersonels($congress_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $personels = $this->adminServices->getListPersonelsByAdmin($congress_id);

        return response()->json($personels);

    }

    public function addPersonnel(Request $request, $congress_id)
    {

        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $admin = $request->input('admin');

        // if exists then update or create admin in DB
        if (!($fetched = $this->adminServices->getAdminByLogin($admin['email']))) {
            $admin = $this->adminServices->addPersonnel($admin);
            $admin_id = $admin->admin_id;
        } else {
            $admin_id = $fetched->admin_id;
            // check if he has already privilege to congress
            $admin_congress = $this->privilegeServices->checkIfAdminOfCongress($admin_id,
                $congress_id);

            if ($admin_congress) {
                return response()->json(['error' => 'Organisateur existant'], 505);
            }
            // else edit changed infos while creating

            $admin['admin_id'] = $admin_id;
            $this->adminServices->editPersonnel($admin);
        }

        $privilegeId = (int)$request->input('privilege_id');
        $congress = $this->congressService->getById($congress_id);
        //create admin congress bind privilege admin and congress
        $admin_congress = $this->privilegeServices->affectPrivilegeToAdmin(
            $privilegeId,
            $admin_id,
            $congress_id);

        $admin = $this->adminServices->getAdminById($admin_id);
        if ($mailtype = $this->congressService->getMailType('organizer_creation')) {
            if (!$mail = $this->congressService->getMail($congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme Eventizer";
            }

            $badgeIdGenerator = $this->congressService->getBadgeByPrivilegeId($congress, $privilegeId);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    $admin->name,
                    $admin->passwordDecrypt);
                $fileAttached = true;
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
        $this->adminServices->editPersonnel($admin);
        $this->privilegeServices->editPrivilege(
            (int)$request->input('privilege_id'),
            $admin_id,
            $congress_id);
        //message d'erreur à revoir
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

    public
    function eliminateInscription($congressId)
    {
        $users = $this->userServices->getUsersByCongressWithAccess($congressId);
        Log::info($users);
        foreach ($users as $user) {
            $access1 = 0;
            $access2 = 0;
            foreach ($user->accesss as $access) {
                if ($access->access_id == 2 || $access->access_id == 3 || $access->access_id == 4) {
                    if ($access1 != 0) {
                        $access->delete();
                    }
                    $access1 = 1;

                }
                if ($access->access_id == 5 || $access->access_id == 6 || $access->access_id == 7) {
                    if ($access2 != 0) {
                        $access->delete();
                    }
                    $access2 = 1;
                }
            }
        }
        return response()->json(['message' => 'success']);

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


        $admin_congress = $this->privilegeServices->checkIfAdminOfCongress($adminId,
            $congressId);

        if ($mailtype = $this->congressService->getMailType('organizer_creation')) {
            if (!$mail = $this->congressService->getMail($congressId, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme Eventizer";
            }

            $badgeIdGenerator = $this->congressService->getBadgeByPrivilegeId($congress, $admin_congress->privilege_id);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    $admin->name,
                    $admin->passwordDecrypt);
                $fileAttached = true;
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

    public function getAdminById($adminId)
    {
        $admin = $this->adminServices->getAdminById($adminId);
        if (!$admin) {
            return response()->json(['response' => 'admin not found'], 404);
        } else {
            return $admin;
        }
    }

    public function getClienthistoriesbyId($adminId)
    {
        return $this->adminServices->getClienthistoriesbyId($adminId);
    }

    public function getClientcongressesbyId($adminId)
    {
        return $this->adminServices->getClientcongressesbyId($adminId);
    }

    public function delete($adminId)
    {
        $admin = $this->adminServices->getAdminById($adminId);
        if (!$admin) {
            return response()->json(['response' => 'admin not found'], 404);
        } elseif ($admin) {
            $admin->delete();
        }
        return response()->json(['response' => 'admin deleted'], 202);
    }

    public function store(Request $request, $pack_id)
    {
        if (!$request->has(['name', 'mobile', 'email'])) {
            return response()->json(['response' => 'invalid request',
                'content' => ['name', 'mobile', 'email']], 400);
        }

        $admin = $this->adminServices->getAdminByMail($request->input('email'));
        if ($admin) {
            return response()->json(['response' => 'admin with same mail found'], 404);
        } else {
            $admin = new Admin();
            $pack = $this->packAdminServices->getPackById($pack_id);
            $history = new HistoryPack();
            $payment = new PaymentAdmin();
            $admin = $this->adminServices->AddAdmin($request, $admin);
            $this->adminServices->addPayment($payment, $admin, $pack);
            $this->adminServices->addHistory($history, $admin, $pack);
            return response()->json(['response' => 'admin added with payment and history'], 202);
        }
    }

    public function update(Request $request, $admin_id)
    {
        $admin = $this->adminServices->getAdminById($admin_id);
        if (!$admin) {
            return response()->json(['response' => 'Admin not found'], 404);
        }
        return response()->json($this->adminServices->updateAdmin($request, $admin), 202);
    }

    public function ActivatePackForAdmin($admin_id, $pack_id, $history_id)
    {
        $newhistory = new HistoryPack();
        $previoushistory = $this->adminServices->gethistorybyId($history_id);
        $pack = $this->packAdminServices->getPackById($pack_id);
        $admin = $this->adminServices->getAdminById($admin_id);
        $this->adminServices->addValidatedHistory($newhistory, $admin, $pack, $previoushistory);
        return response()->json(['response' => 'pack Activated , new  history entry created'], 202);
    }

    public function addHistoryToAdmin(Request $request)
    {
        $newhistory = new HistoryPack();
        $this->adminServices->addPackToAdmin($request, $newhistory);
        return response()->json(['response' => 'pack Added , new  history entry created'], 202);

    }
}
