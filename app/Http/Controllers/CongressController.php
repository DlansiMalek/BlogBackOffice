<?php

namespace App\Http\Controllers;


use App\Models\Congress;
use App\Models\Custom_Mail;
use App\Models\User;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\PackServices;
use App\Services\PrivilegeServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


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

    function __construct(CongressServices $congressServices, AdminServices $adminServices,
                         AccessServices $accessServices,
                         PrivilegeServices $privilegeServices,
                         UserServices $userServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         PackServices $packService)
    {
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->accessServices = $accessServices;
        $this->privilegeServices = $privilegeServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->packService = $packService;
    }


    public function addCongress(Request $request)
    {
        $congress = $this->addFullCongress($request);

        return response()->json(["message" => "add congress sucess",
            "data" => $this->congressServices->getCongressById($congress->congress_id)]);

    }

    public function editCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        $admin = $this->adminServices->retrieveAdminFromToken();

        if (!$this->isAllowedEdit($congress->congress_id)) {
            return response()->json(['error' => 'edit not allowed'], 401);
        }

        $congress = $this->congressServices->editCongress($congress, $admin->admin_id, $request);

        $accesses = $this->accessServices->addAccessToCongress($congress->congress_id, $request->input("accesss"));

        $this->packService->addPacks($accesses, $request->input("packs"), $congress);

        $this->congressServices->addFormInputs($request->input("form_inputs"), $congress->congress_id);

        return response()->json(["message" => "edit congress success"]);
    }

    public function getCongressById($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        return response()->json($congress);
    }

    private function addFullCongress(Request $request)
    {
        $admin = $this->adminServices->retrieveAdminFromToken();

        $congress = $this->congressServices->addCongress(
            $request->input("name"),
            $request->input("date"),
            $request->input("username_mail"),
            $request->input("object_mail_inscription"),
            $request->input("object_mail_payement"),
            $request->input("object_mail_attestation"),
            $admin->admin_id);
        $accesses = $this->accessServices->addAccessToCongress($congress->congress_id, $request->input("accesss"));
        $this->packService->addPacks($accesses, $request->input("packs"), $congress);
        $this->congressServices->addFormInputs($request->input('form_inputs'), $congress->congress_id);
        return $congress;
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

        foreach ($users as $user) {
            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                    $user->qr_code);
                $this->userServices->sendMail($congress->mail_inscription, $user, $congress, $congress->object_mail_inscription);
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
            }
        }
        return response()->json(['message' => 'send mail successs']);
    }

    public function uploadLogo($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $congress = $this->congressServices->uploadLogo($congress, $request);

        return response()->json($congress);
    }

    public function saveMail(Request $request, $congress_id, $mode)
    {
        if (!$request->has(['object', 'template'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['object', 'template']], 400);
        } else if ($mode == 'inscription' || $mode == 'paiement') {
            $congress = $this->congressServices->getCongressById($congress_id);
            if ($mode == 'inscription') {
                $congress->object_mail_inscription = $request->input('object');
                $congress->mail_inscription = $request->input('template');
            } else {
                $congress->object_mail_payement = $request->input('object');
                $congress->mail_payement = $request->input('template');
            }
            $congress->save();
            return $congress;
        } else if ($mode == "new") {
            $mail = new Custom_Mail();
            $mail->object = $request->input('object');
            $mail->template = $request->input('template');
            $mail->congress_id = $congress_id;
            $this->congressServices->saveCustomMail($mail);
            return $mail;
        } else {
            if (!is_numeric($mode))
                return response()->json(['resposne' => 'bad request', 'error' => 'wrong id'], 400);
            $email = $this->congressServices->getEmailById($mode);
            $email->object = $request->input("object");
            $email->template = $request->input("template");
            $this->congressServices->saveCustomMail($email);
            return $email;

        }
    }

}
