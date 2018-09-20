<?php

namespace App\Http\Controllers;


use App\Services\AccessServices;
use App\Services\AddInfoServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\PrivilegeServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class CongressController extends Controller
{

    protected $congressServices;
    protected $adminServices;
    protected $addInfoServices;
    protected $accessServices;
    protected $privilegeServices;
    protected $userServices;
    protected $sharedServices;

    function __construct(CongressServices $congressServices, AdminServices $adminServices,
                         AddInfoServices $addInfoServices,
                         AccessServices $accessServices,
                         PrivilegeServices $privilegeServices,
                         UserServices $userServices,
                         SharedServices $sharedServices)
    {
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->addInfoServices = $addInfoServices;
        $this->accessServices = $accessServices;
        $this->privilegeServices = $privilegeServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
    }


    public function addCongress(Request $request)
    {
        $congress = $this->addFullCongress($request);


        return response()->json(["message" => "add congress sucess", "data" => $this->congressServices->getCongressById($congress->congress_id)]);


    }

    public function editCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        $admin = $this->adminServices->retrieveAdminFromToken();

        $congress = $this->congressServices->editCongress($congress, $admin->admin_id, $request);

        $this->adminServices->addResponsibleCongress($request->input("responsibleIds"), $congress->congress_id);
        $this->addInfoServices->addInfoToCongress($congress->congress_id, $request->input("addInfoIds"));
        $this->accessServices->addAccessToCongress($congress->congress_id, $request->input("accesss"));

        //$this->addFullCongress($request);

        return response()->json(["message" => "edit congress success"]);
    }

    public function getCongressById($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }

        return response()->json($congress);
    }

    private function addFullCongress($request)
    {
        $admin = $this->adminServices->retrieveAdminFromToken();

        $congress = $this->congressServices->addCongress($request->input("name"), $request->input("date"), $admin->admin_id);
        $this->adminServices->addResponsibleCongress($request->input("responsibleIds"), $congress->congress_id);
        $this->addInfoServices->addInfoToCongress($congress->congress_id, $request->input("addInfoIds"));
        $this->accessServices->addAccessToCongress($congress->congress_id, $request->input("accesss"));

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
            //return response()->json($this->congressServices->getCongressAllowedAccess($admin->admin_id));
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
            $this->sharedServices->saveFileInPublic($congress->badge->badge_id_generator,
                ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                $user->qr_code);
            $this->userServices->sendMail($user, $congress);
        }

        return response()->json(['message' => 'send mail successs']);
    }

}
