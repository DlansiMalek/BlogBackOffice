<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\OrganizationServices;
use App\Services\PaymentServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{

    protected $organizationServices;
    protected $congressServices;
    protected $adminServices;
    protected $userServices;
    protected $sharedServices;
    protected $paymentServices;
    protected $accessServices;


    function __construct(OrganizationServices $organizationServices,
                         CongressServices $congressServices,
                         AdminServices $adminServices,
                         UserServices $userServices,
                         SharedServices $sharedServices,
                         PaymentServices $paymentServices,
                         AccessServices $accessServices)
    {
        $this->organizationServices = $organizationServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
        $this->paymentServices = $paymentServices;
        $this->accessServices = $accessServices;
    }

    public function addOrganization($congress_id, Request $request)
    {
        $privilegeId = 7; //Privilegg Organisme;
        if (!$request->has(['email', 'name'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['email', 'nom']], 404);
        }

        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        $admin = $this->adminServices->getAdminByMail($request->input("email"));
        if (!$admin) {
            $admin = $this->adminServices->addPersonnel($request);
        } else {
            if ($this->adminServices->checkHasPrivilegeByCongress($admin->admin_id, $congress_id)) {
                return response()->json(['error' => 'admin alerady has a privilege in this congress'], 500);
            }
        }

        $organization = $this->organizationServices->getOrganizationByName($request->input("name"));
        if (!$organization) {
            $organization = $this->organizationServices->addOrganization($request, $admin->admin_id);
        } else {
            if ($this->organizationServices->getOrganizationByCongressIdAndOrgId($congress_id, $organization->organization_id)) {
                return response()->json(["message" => "organization already exists in this congress"], 401);
            }
            $organization->admin_id = $admin->admin_id;
            $organization->update();
        }

        // PrivilegeID = 7 : Organisme
        $this->adminServices->addAdminCongress($admin->admin_id, $congress_id, $privilegeId);


        $this->organizationServices->affectOrganizationToCongress($congress_id, $organization->organization_id);

        if ($mailtype = $this->congressServices->getMailType('organization')) {
            if (!$mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme VayeCongress";
            }

            $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $privilegeId);
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                    strtoupper($organization->name),
                    $admin->passwordDecrypt);
                $fileAttached = true;
            }
            $mail->template = $mail->template . "<br>Votre Email pour accéder à la plateforme <a href='https://congress.vayetek.com'>Eventizer</a>: " . $admin->email;
            $mail->template = $mail->template . "<br>Votre mot de passe pour accéder à la plateforme <a href='https://congress.vayetek.com'>Eventizer</a>: " . $admin->passwordDecrypt;

            $this->adminServices->sendMail($this->congressServices->renderMail($mail->template, $congress, null, null, $organization, null), $congress, $mail->object, $admin, $fileAttached);
        }

        return response()->json($organization);
    }

    public function getCongressOrganizations($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(["message" => "congress not found"], 404);

        $organizations = $this->organizationServices->getOrganizationsByCongressId($congress_id);

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

    public function getOrganizationById($admin_id)
    {
        return $this->organizationServices->getOrganizationById($admin_id);
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

        if ($user->organization_id != $organization->organization_id)
            return response()->json(["message" => "user does not belong to organization"], 401);

        if ($user->organization_accepted) return $organization;

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
        $badgeIdGenerator = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
        $fileAttached = false;
        if ($badgeIdGenerator != null) {
            $this->sharedServices->saveBadgeInPublic($badgeIdGenerator,
                ucfirst($user->first_name) . " " . strtoupper($user->last_name),
                $user->qr_code);
            $fileAttached = true;
        }


        if ($mailtype = $this->congressServices->getMailType('subvention')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, $organization, null), $user, $congress, $mail->object, null);
            }
        }

        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, $fileAttached);
            }
        }
    }

    function getOrganizationByAdminIdAndCongressId($adminId, $congressId)
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

    function saveAllUsersOrganization($organizationId, $congressId, Request $request)
    {
        ini_set('max_execution_time', 500); //3 minutes

        $congress = $this->congressServices->getById($congressId);
        $users = $request->all();
        //PrivilegeId = 3
        $sum = 0;

        // Affect All Access Free (To All Users)
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 0);
        $accessIds = $this->accessServices->getAccessIdsByAccess($accessNotInRegister);
        foreach ($users as $userData) {
            if ($userData['email'] && $userData['first_name'] && $userData['last_name']) {
                $privilegeId = 3;
                $request->merge(['privilege_id' => $privilegeId, 'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email']]);
                // Get User per mail
                if (!$user = $this->userServices->getUserByEmail($userData['email'])) {
                    $user = $this->userServices->saveUser($request);
                }
                // Check if User already registed to congress
                if (!$user_congress = $this->userServices->getUserCongress($congressId, $user->user_id)) {
                    $user_congress = $this->userServices->saveUserCongress($congressId, $user->user_id, $request);
                }
                $user_congress->organization_id = $organizationId;
                $user_congress->organization_accepted = true;
                $user_congress->update();


                $this->userServices->deleteAccess($user->user_id, $accessIds);
                $this->userServices->affectAccessElement($user->user_id, $accessNotInRegister);

                if (!$userPayment = $this->userServices->getPaymentInfoByUserAndCongress($user->user_id, $congressId)) {
                    $userPayment = $this->paymentServices->affectPaymentToUser($user->user_id, $congressId, $congress->price, false);
                }

                $sum += $userPayment->price;
            }
        }


        $congressOrganization = $this->organizationServices->getOrganizationByCongressIdAndOrgId($congressId, $organizationId);
        $congressOrganization->montant = $congressOrganization->montant + $sum;
        $congressOrganization->update();

        return response()->json($this->organizationServices->getAllUserByOrganizationId($organizationId, $congressId));

    }


    function getAllUserByOrganizationId($organizationId, $congressId)
    {
        return response()->json($this->organizationServices->getAllUserByOrganizationId($organizationId, $congressId));
    }
}
