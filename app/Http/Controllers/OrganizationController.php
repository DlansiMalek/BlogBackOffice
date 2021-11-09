<?php

namespace App\Http\Controllers;

use App\Services\Utils;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OrganizationServices;
use App\Services\PaymentServices;
use App\Services\SharedServices;
use App\Services\StandServices;
use App\Services\PrivilegeServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{

    protected $organizationServices;
    protected $congressServices;
    protected $adminServices;
    protected $userServices;
    protected $sharedServices;
    protected $paymentServices;
    protected $accessServices;
    protected $mailServices;
    protected $standServices;
    protected $privilegeServices;
    public function __construct(OrganizationServices $organizationServices,
        CongressServices $congressServices,
        AdminServices $adminServices,
        UserServices $userServices,
        SharedServices $sharedServices,
        PaymentServices $paymentServices,
        AccessServices $accessServices,
        MailServices $mailServices,
        StandServices $standServices,
        PrivilegeServices $privilegeServices) {
        $this->organizationServices = $organizationServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
        $this->paymentServices = $paymentServices;
        $this->accessServices = $accessServices;
        $this->mailServices = $mailServices;
        $this->standServices = $standServices;
        $this->privilegeServices = $privilegeServices;
    }

    public function addOrganization($congress_id, Request $request)
    {
        if (!$request->has(['name'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['name']], 404);
        }
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        $organization = null;
        if ($request->has('organization_id')) {
            $organization = $this->organizationServices->getOrganizationById($request->input('organization_id'));
        }
        $organization = $this->organizationServices->addOrganization($organization, $congress_id, $request);
        $stand = null;
        $request['organization_id'] = $organization->organization_id;
        if ($request->has('stand')) {
            if ($request->input('stands')['0']['stand_id']) {
                $stand = $this->standServices->getStandById($request->input('stands')['0']['stand_id']);
            } else {
                $stand = $this->standServices->addStand(null, $congress_id, $request);
            } 
            if ($stand) {
                $standDocs = $request->input('stand')['docs'];
                $this->standServices->saveResourceStand($standDocs, $stand->stand_id);
            }
        } 
        $privilegeId = 7;
        $password = Str::random(8);
        if (!($old_admin = $this->adminServices->getAdminByLogin($organization->email))) {
            $admin = $this->adminServices->addPersonnel($request, $password);
            $admin_congress = $this->privilegeServices->affectPrivilegeToAdmin($privilegeId, $admin->admin_id, $congress_id);
        } else {
            $admin = $this->adminServices->editPersonnel($request, $old_admin);
            if (!$admin_congress = $this->privilegeServices->checkIfAdminOfCongress($admin->admin_id, $congress_id)) {
                $admin_congress = $this->privilegeServices->affectPrivilegeToAdmin($privilegeId, $admin->admin_id, $congress_id);
            }
        }
        if (!$user = $this->userServices->getUserByEmail($organization->email)) {
            $admin= Utils::explodeString($admin);
            $user = $this->userServices->addUserFromExcel($admin, $password);
            $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, $organization->organization_id, null);
        } else {
            if (!$user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
                $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, $organization->organization_id, null);
            }
        }
        $linkBackOffice = UrlUtils::getUrlEventizerWeb();
        if ($mailtype = $this->congressServices->getMailType('organization')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->adminServices->sendMail($this->adminServices->renderMail($mail->template, $admin, null, null, $linkBackOffice), $congress, $mail->object, $admin, null);
            } else {
                if ($mail = $this->congressServices->getMailOutOfCongress($mailtype->mail_type_id)) {
                    $this->adminServices->sendMail($this->adminServices->renderMail($mail->template, $admin, null, null, $linkBackOffice), $congress, $mail->object, $admin, null);
                }
            }
        }
        return response()->json($this->organizationServices->getOrganizationById($organization->organization_id));
    }

    public function deleteOrganization($congress_id, $organization_id)
    {
        if (!$organization = $this->organizationServices->getOrganizationById($organization_id)) {
            return response()->json('no organization found', 404);
        }

        $this->organizationServices->deleteOrganization($organization);
        return response()->json(['response' => 'organization deleted'], 200);
    }

    public function getCongressOrganizations($congress_id , Request $request)
    {
        $admin = $this->adminServices->retrieveAdminFromToken();
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }
        $admin_congress = $this->adminServices->checkHasPrivilegeByCongress($admin->admin_id, $congress_id);
        $organizations = $this->organizationServices->getOrganizationsByCongressId($congress_id, $admin->email,  $admin_congress->privilege_id);
        return response()->json($organizations);
    }

    public function getCongress($admin_id)
    {
        $organization = $this->organizationServices->getOrganizationByAdminId($admin_id);
        return $this->congressServices->getCongressById($organization->congress_organization->congress_id);
    }

    public function getOrganizationByAdminId($admin_id)
    {
        return $this->organizationServices->getOrganizationByAdminId($admin_id);
    }

    public function getOrganizationById($organization_id, $congress_id)
    {
        return $this->organizationServices->getOrganizationById($organization_id);
    }

    public function acceptAllParticipants($organization_id)
    {
        $organization = $this->organizationServices->getOrganizationById($organization_id);
        $organization->congress_organization->montant = 0;
        $congress = $this->congressServices->getCongressById($organization->congress_organization->congress_id);
        foreach ($organization->users as $user) {
            $organization->congress_organization->montant += $user->price;
            if (!$user->organization_accepted || !$user->isPaid) {
                $user->organization_accepted = true;
                $user->isPaid = 1;
                $user->update();
                $this->sendMail($congress, $user);
            }

        }
        $organization->congress_organization->update();
        return $organization;
    }

    public function acceptParticipant($organization_id, $user_id)
    {
        $organization = $this->organizationServices->getOrganizationById($organization_id);
        $user = $this->userServices->getUserById($user_id);

        if ($user->organization_id != $organization->organization_id) {
            return response()->json(["message" => "user does not belong to organization"], 401);
        }

        if ($user->organization_accepted) {
            return $organization;
        }

        $organization->congress_organization->montant += $user->price;
        $user->organization_accepted = true;
        $user->isPaid = 1;
        $user->update();
        $organization->congress_organization->update();
        $congress = $this->congressServices->getCongressById($organization->congress_organization->congress_id);
        $this->sendMail($congress, $user);
        return $this->organizationServices->getOrganizationById($organization->organization_id);
    }

    private function sendMail($congress, $user)
    {
        $organization = $this->organizationServices->getOrganizationById($user->organization_id);
        $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
        $badgeIdGenerator = $badge['badge_id_generator'];

        $fileAttached = false;
        $fileName = "badge.png";
        if ($badgeIdGenerator != null) {
            $fileAttached = $this->sharedServices->saveBadgeInPublic($badge,
                $user,
                $user->qr_code,
                $user->privilege_id,
                $congress->congress_id);
        }

        if ($mailtype = $this->congressServices->getMailType('subvention')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, $organization, null), $user, $congress, $mail->object, null);
            }
        }

        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
            $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
            $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . "/room/".$congress->congress_id.'/event-room';
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice,null,null,null,null,null,null,null,[],null,null,$linkPrincipalRoom), $user, $congress, $mail->object, $fileAttached, null, null ,$fileName);
            }
        }
    }

    public function getOrganizationByAdminIdAndCongressId($adminId, $congressId)
    {
        $organizations = $this->organizationServices->getOrganizationsByCongressId($congressId);

        $org = null;
        for ($i = 0; $i < sizeof($organizations); $i++) {
            if ($organizations[$i]->admin->admin_id == $adminId) {
                $org = $organizations[$i];
                break;
            }
        }
        return response()->json(['users' => $this->organizationServices->getAllUserByOrganizationId($org->organization_id, $congressId), 'organization' => $org]);
    }

    public function getAllUserByOrganizationId($organizationId, $congressId)
    {
        return response()->json($this->organizationServices->getAllUserByOrganizationId($organizationId, $congressId));
    }

    public function getSponsorsByCongressId($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        $organizations = $this->organizationServices->getSponsorsByCongressId($congress_id);

        return response()->json($organizations);
    }

    public function saveOrganizationsFromExcel($congressId, Request $request)
    {
        ini_set('max_execution_time', 500); //3 minutes
        $data = $request->input("data");
        foreach ($data as $org) {
            if ($org['organization_name']) {
                $newAdmin = null;
                if (isset($org['admin_name']) && isset($org['admin_email'])) {
                    // Add Admin 
                    $adminByEmail = $this->adminServices->getAdminByMail($org['admin_email']);
                    $newAdmin = $this->adminServices->addAdminFromExcel($adminByEmail, $org);
                    $adminCongress = null;
                    if ($adminByEmail) {
                        $adminCongress = $this->adminServices->checkHasPrivilegeByCongress($adminByEmail->admin_id, $congressId);
                    }
                    $this->adminServices->addAdminCongressFromExcel($adminCongress, $newAdmin->admin_id, $congressId, 7);
                    // Add User
                    $user_by_mail = $this->userServices->getUserByEmail($org['admin_email']);
                    $newUser = $this->userServices->addUserFromExcelOrgnization($user_by_mail, $org);
                    $userCongress = null;
                    if ($user_by_mail) {
                        $userCongress = $this->userServices->getUserCongress($congressId, $user_by_mail->user_id);
                    }
                    $this->userServices->addUserCongressFromExcelOrgnization($userCongress, $newUser->user_id, $congressId, 7);
                }
                // Add Organization & Stand
                $newOrg = $this->organizationServices->getOrganizationByNameAndCongress($org['organization_name'], $congressId);
                $newOrganization = $this->organizationServices->addOrganizationFromExcel($newOrg, $org, $congressId, $newAdmin);
                $stand = $this->standServices->getStandByCongressIdOrgizantionIdAndName($org['organization_name'], $congressId, $newOrganization->organization_id);
                $this->standServices->addStandFromExcel($stand, $org['organization_name'], $congressId, $newOrganization->organization_id);
            }
        }
        return response()->json($this->organizationServices->getOrganizationsByCongressId($congressId));

    }

}
