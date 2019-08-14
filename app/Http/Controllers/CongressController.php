<?php

namespace App\Http\Controllers;


use App\Models\Badge;
use App\Models\ConfigCongress;
use App\Models\Congress;
use App\Models\Mail;
use App\Models\User;
use App\Models\Access;
use App\Models\UserMail;
use App\Models\UserCongress;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\GeoServices;
use App\Services\PackServices;
use App\Services\PrivilegeServices;
use App\Services\ResourcesServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\MailServices;
use App\Services\Utils;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CongressController extends Controller
{

    protected $congressServices;
    protected $adminServices;
    protected $accessServices;
    protected $privilegeServices;
    protected $userServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $packService;
    protected $resourceService;
    protected $geoServices;
    protected $mailServices ;
    public $baseUrl = "http://localhost/congress-backend-modules/public/api/";

//    public $baseUrl = "https://congress-api.vayetek.com/api/";

    function __construct(CongressServices $congressServices, AdminServices $adminServices,
                         AccessServices $accessServices,
                         PrivilegeServices $privilegeServices,
                         UserServices $userServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         PackServices $packService,
                         GeoServices $geoServices,
                         MailServices $mailServices,
                         ResourcesServices $resourceService)
    {
        $this->congressServices = $congressServices;
        $this->geoServices = $geoServices;
        $this->adminServices = $adminServices;
        $this->accessServices = $accessServices;
        $this->privilegeServices = $privilegeServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->packService = $packService;
        $this->resourceService = $resourceService;
        $this->mailServices = $mailServices;
    }


    public function addCongress(Request $request)
    {
        if (!$request->has(['name', 'start_date', 'end_date', 'price', 'config']))
            return response()->json(['message' => 'bad request'], 400);
        $admin = $this->adminServices->retrieveAdminFromToken();
        return $this->congressServices->addCongress(
            $request->input("name"),
            $request->input("start_date"),
            $request->input("end_date"),
            $request->input('price'),
            $request->input('config')['has_payment'],
            $request->input('config')['free'],
            $request->input('config')['prise_charge_option'],
            $request->input('description'),
            $admin->admin_id);
    }

    public function editConfigCongress(Request $request, $congressId)
    {

        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $congress = $this->congressServices->editConfigCongress($request->input('congress'), $request->input('eventLocation'), $congressId);
        return response()->json($congress);

    }

    public function editCongress(Request $request, $congressId)
    {
        if (!$request->has(['name', 'start_date']))
            return response()->json(['message' => 'bad request'], 400);
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["message" => "congress not found"], 404);
        }
        if (!$config = ConfigCongress::where('congress_id', '=', $congressId)->first())
            $config = new ConfigCongress();

        $congress = $this->congressServices->editCongress($congress, $config, $request);

        return response()->json($congress);
    }

    public function getCongressById($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        return response()->json($congress);
    }

    public function getCongressConfigById($congress_id)
    {
        if (!$configCongress = $this->congressServices->getCongressConfigById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $location = $this->geoServices->getCongressLocationByCongressId($congress_id);
        return response()->json([$configCongress, $location]);
    }


    /**
     * @SWG\Get(
     *   path="/mobile/congress",
     *   tags={"Mobile"},
     *   summary="getCongressByAdmin",
     *   operationId="getCongressByAdmin",
     *   security={
     *     {"Bearer": {}}
     *   },
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function getCongressByAdmin()
    {
        $admin = $this->adminServices->retrieveAdminFromToken();
        if ($admin_priv = $this->privilegeServices->checkIfHasPrivilege(1, $admin->admin_id)) {
            return response()->json($this->congressServices->getCongressAllAccess($admin->admin_id));
        }

        if ($admin_priv = $this->privilegeServices->checkIfHasPrivilege(2, $admin->admin_id)) {
            return response()->json($this->congressServices->getCongressAllAccess($admin->responsible));
        }

        return response()->json(["message" => "bizzare"]);
    }

    public function getBadgesByCongress($congressId)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        $badgeName = $congress->badge_name;
        $users = $this->userServices->getAllowedBadgeUsersByCongress($congressId);
        $users->each(function ($user) {
            $user->update(['isBadgeGeted' => 1]);
        });

        if (sizeof($users) == 0) {
            return response(['message' => 'not even user'], 404);
        }

        return $this->congressServices->getBadgesByUsers($badgeName, $users);
    }

    public function sendMailAllParticipants($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congres not found'], 404);
        }
        $users = $this->userServices->getUsersEmailNotSendedByCongress($congressId);

        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
            if ($mail = $this->congressServices->getMail($congressId, $mailtype->mail_type_id)) {
                foreach ($users as $user) {
                    $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
                    if ($badgeIdGenerator != null) {
                        $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                            ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                            $user->qr_code);
                        $this->userServices->sendMail($this->congressServices
                            ->renderMail($mail->template, $congress, $user, null, null),
                            $user, $congress, $mail->object, true,
                            null);
                    }
                }
            }

        }


        return response()->json(['message' => 'send mail successs']);
    }

    public function getAttestationDiversByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        return response()->json($this->badgeServices->getAttestationDiversByCongress($congressId));
    }

    private function isAllowedEdit($congress_id)
    {
        $users = User::where('congress_id', '=', $congress_id)
            ->get();

        return sizeof($users) == 0;
    }

    public function getLabsByCongress($congress_id)
    {
        $labs = $this->congressServices->getLabsByCongress($congress_id);
        return $labs;
    }

    public function getOrganizationInvoice($congressId, $labId)
    {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congres not found'], 404);
        }
        return $this->congressServices->getOrganizationInvoiceByCongress($labId, $congress);
    }

    public function sendMailAllParticipantsAttestation($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        $users = $this->userServices->getUsersEmailAttestationNotSendedByCongress($congressId);
        foreach ($users as $user) {
            $request = array();
            if ($user->email != null && $user->email != "-" && $user->email != "" && $user->isPresent == 1) {
                if ($congress->attestation) {
                    array_push($request,
                        array(
                            'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false
                        ));
                }
                foreach ($user->accesss as $access) {
                    if ($access->pivot->isPresent == 1 && $access->attestation) {
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

                $mailtype = $this->congressServices->getMailType('attestation');
                $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

                if ($mail) {
                    $this->badgeServices->saveAttestationsInPublic($request);
                    $this->userServices->sendMailAttesationToUser($user, $congress, $mail->object,
                        $this->congressServices->renderMail($mail->template, $congress, $user, null, null));
                }
            }
        }
        return response()->json(['message' => 'send mail successs']);
    }

    public function uploadLogo($congress_id, Request $request)
    {

        if (!$congressConfig = $this->congressServices->getCongressConfig($congress_id)) {

            return response()->json(['error' => 'congress not found'], 404);
        }
        $this->congressServices->uploadLogo($request->file('file_data'), $congressConfig);

        return response()->json($this->congressServices->getCongressById($congress_id));
    }

    public function uploadBanner($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressConfigById($congressId)) {
            return response()->json(['error' => 'congress not found '], 404);
        }
        $this->congressServices->uploadBanner($request->file('file_data'), $congress);

        return response()->json($this->congressServices->getCongressById($congressId));
    }

    public function getAllCongresses()
    {
        return $this->congressServices->getAllCongresses();
    }


    public function sendCustomMailToAllUsers($mail_id)
    {
        if (!$mail = $this->congressServices->getEmailById($mail_id))
            return response()->json(['response' => 'mail not found'], 404);
        $congress = $this->congressServices->getCongressById($mail->congress_id);
        foreach ($congress->users as $user) {
            $userMail = UserMail::where('user_id', '=', $user->user_id)
                ->where('mail_id', '=', $mail->mail_id)
                ->first();

            if (!$userMail) {
                $userMail = new UserMail();
                $userMail->user_id = $user->user_id;
                $userMail->mail_id = $mail_id;
                $userMail->save();
            } else if ($userMail->status == 1) {
                $userMail = null;
            } else {
                $userMail->status = 1;
                $userMail->update();
            }

            if ($userMail) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null), $user, $congress, $mail->object, false,
                    null, $userMail);
            }


        }

        return response()->json(["status" => "success"], 200);
    }

    public function setProgramLink(Request $request, $congress_id)
    {
        if (!$request->has("programLink")) return response()->json(['error' => 'invalid request'], 400);
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(['error' => 'congress not found'], 402);
        $congress->program_link = $request->input('programLink');
        $congress->update();
        return $congress;
    }

    public function getAll()
    {


    }

    function getParticipantsCounts(Request $request)
    {
        $result = [];
        foreach ($request->all() as $congress_id) {
            if (!$this->congressServices->getCongressById($congress_id))
                return response()->json(['message' => 'congresses not found'], 404);
            array_push($result, (object)[
                'congress_id' => $congress_id,
                'count' => $this->congressServices->getParticipantsCount($congress_id)
            ]);
        }
        return $result;
    }

    function getLogo($congress_id)
    {
        if (!$config = $this->congressServices->getCongressConfig($congress_id)) return response()->json(['response' => 'congress not found'], 404);
        if (!$config->logo) return response()->json(['response' => 'no logo'], 400);
        return Storage::download($config->logo);
    }

    function getBanner($congress_id)
    {
        if (!$config = $this->congressServices->getCongressConfig($congress_id)) return response()->json(['response' => 'congress not found'], 404);
        if (!$config->banner) return response()->json(['response' => 'no logo'], 400);
        return Storage::download($config->banner);
    }

    function addDemo(){
        // add congrees , congress_config , Admin_Congress
        $this->congressServices->addCongress("DemoCongress",date('Y-m-d'),date('Y-m-d'),99,
            true,false,true,"this is the Demo descrtiption",3);
        $congress = $this->congressServices->getDemoCongress("DemoCongress");
        // add users
     /*   $user = $this->userServices->getParticipatorById(1); //user exemple 'UserVayetek'
        $userCongress = new UserCongress();
        $userCongress->congress_id = $congress->congress_id;
        $userCongress->user_id = $user->user_id;
        $userCongress->privilege_id = 3;    //privilege particiapant
        $userCongress->save();*/
        //add badges
        $badge =  new Badge();
        $badge->badge_id_generator = "5c6dbd67d2cb3900015d7a65";
        $badge->privilege_id = 3;
        $badge->congress_id = $congress->congress_id;
        $badge->save();
        //add access
        $access = new Access();
        $access->name = "AccesDemo" ;
        $access->start_date = date('Y-m-d') ;
        $access->end_date = date('Y-m-d');
        $access->access_type_id = 1 ; // type session
        $access->price = 99;
        $access->packless = 1 ;
        $access->description = " description de l'access demo";
        $access->seuil = 99;
         $access->max_places = 100 ;
        $access->congress_id = $congress->congress_id;
        $access->save();
        //ad mail
        $this->mailServices->saveMail($congress->congress_id,1,"inscription",'<p>Veuillez cliquer sur ce lien afin de valider votre paiement.</p><p><a href="{{%24link}}">Lien</a></p>');
        $this->mailServices->saveMail($congress->congress_id,2,"Paiement","Veuillez cliquer sur ce lien afin de valider votre paiement");
        $this->mailServices->saveMail($congress->congress_id,5,"Confirmation","Voullez vous vraiment confirmer Cette action");

        return response()->json(["status" => "success added demo congress"], 200);
    }
}