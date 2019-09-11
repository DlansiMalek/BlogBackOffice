<?php

namespace App\Http\Controllers;


use App\Models\Mail;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\OrganizationServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{

    protected $organizationServices;
    protected $congressServices;
    protected $adminServices;
    protected $userServices;
    protected $sharedServices;


    function __construct(OrganizationServices $organizationServices,
                         CongressServices $congressServices,
                         AdminServices $adminServices,
                         UserServices $userServices,
                         SharedServices $sharedServices)
    {
        $this->organizationServices = $organizationServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
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
            if ($this->organizationServices->exist($congress_id, $organization->organization_id)) {
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

        $link = Utils::baseUrlWEB . "/#/auth/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
        if ($mailtype = $this->congressServices->getMailType('subvention')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, $link, $organization, null), $user, $congress, $mail->object, null);
            }
        }

        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, $link, null, null), $user, $congress, $mail->object, $fileAttached);
            }
        }
    }

}
