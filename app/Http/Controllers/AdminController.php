<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\PrivilegeServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
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

    public function __construct(UserServices $userServices,
                                AdminServices $adminServices,
                                CongressServices $congressService,
                                PrivilegeServices $privilegeServices,
                                SharedServices $sharedServices,
                                BadgeServices $badgeServices)
    {
        $this->userServices = $userServices;
        $this->adminServices = $adminServices;
        $this->congressService = $congressService;
        $this->privilegeServices = $privilegeServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
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
            return response()->json(['resposne' => 'bad request', 'required fields' => ['qrcode']], 400);
        }

        $participator = $this->userServices->getParticipatorByQrCode($request->input('qrcode'));

        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }

        foreach ($participator->accesss as $accesss) {
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
    public
    function makeUserPresentAccess(Request $request, $userId)
    {
        //type : 1 : Enter Or 0 : Leave
        if (!$request->has(['isPresent', 'accessId', 'type'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['isPresent', 'accessId']], 400);
        }
        $participator = $this->userServices->getUserById($userId);
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }
        if ($participator->isPresent == 0) {
            return response()->json(['response' => 'participator not present in congress'], 404);
        }

        if (!$user_access = $this->userServices->getUserAccessByUser($participator->user_id, $request->input("accessId"))) {
            return response()->json(["message" => "user not allowed to this access"], 401);
        }
        if ($user_access->isPresent == 0 && $request->input('type') == 0) {
            return response()->json(['message' => 'cannot leave , enter first'], 401);
        }

        $this->userServices->makePresentToAccess($user_access, $participator,
            $request->input('accessId'), $request->input('isPresent'), $request->input('type'));


        //DENTAIRE SHIT
        if ($request->input('accessId') == 8) {
            $user_shit = $this->userServices->getUserAccessByUser($participator->user_id, 25);
            $this->userServices->makePresentToAccess($user_shit, $participator,
                25, $request->input('isPresent'), $request->input('type'));
        }

        return response()->json(["message" => "success sending and scaning"], 200);
    }


    /**
     * @SWG\Get(
     *   path="/admin/me",
     *   summary="Get Admin By Token",
     *   operationId="getAuhentificatedAdmin",
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
    function getAuhentificatedAdmin()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $admin = $this->adminServices->getAdminById($admin->admin_id);

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('admin'));
    }


    /**
     * @SWG\Get(
     *   path="/admin/me/congress",
     *   summary="Get Congress By Admin",
     *   operationId="getAdminCongresses",
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
    function getAdminCongresses()
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
    function getListPersonels()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $personels = $this->adminServices->getListPersonelsByAdmin($admin->admin_id);

        return response()->json($personels);

    }

    public
    function addPersonnel(Request $request)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $personels = $this->adminServices->addPersonnel($request, $admin->admin_id);

        $this->privilegeServices->affectPrivilegeToAdmin(2, $personels->admin_id);

        return response()->json($personels);
    }

    public
    function deletePersonnel($personnelId)
    {
        if (!$admin = $this->adminServices->getAdminById($personnelId)) {
            return response()->json(["message" => "admin not found"], 404);
        }
        $this->adminServices->deleteAdminById($admin);
        return response()->json(["message" => "deleted success"]);

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


        $this->sharedServices->saveFileInPublic($congress->badge->badge_org_id_generator,
            $admin->name,
            $admin->passwordDecrypt);

        $this->userServices->sendCredentialsOrganizerMail($admin);
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

    function getAttestationByUser(request $request)
    {
        if (!$request->has(['rfid'])) {
            return response()->json(['error' => 'bas request'], 400);
        }
        if (!$user = $this->userServices->getUserByRfid($request->input('rfid'))) {
            return response()->json(['error' => 'user not found'], 404);
        }
        return $user;
    }

}
