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

    public
    function addOrganization($congress_id, Request $request)
    {
        if (!$request->has(['email', 'name'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['email', 'nom']], 404);
        }

        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        if (!$organization = $this->organizationServices->addOrganization($request, $congress_id, $admin->admin_id)) {
            return response()->json(["message" => "error adding organization"], 404);
        }


        if ($mailtype = $this->congressServices->getMailType('organization')) {
            if (!$mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme VayeCongress";
            }

            $mail->template = $mail->template . "<br>Votre Email pour accéder à la plateforme <a href='https://congress.vayetek.com'>VayeCongress</a>: " . $organization["admin"]->email;
            $mail->template = $mail->template . "<br>Votre mot de passe pour accéder à la plateforme <a href='https://congress.vayetek.com'>VayeCongress</a>: " . $organization["admin"]->passwordDecrypt;
            $this->organizationServices->sendMail($this->congressServices->renderMail($mail->template, $congress, null, null,$organization['organization']), $congress, $mail->object, $organization["organization"]->email);
        }

        return $organization["organization"];


    }

    public function getCongressOrganizations($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(["message" => "congress not found"], 404);
        return $congress->organizations ? $congress->organizations : [];
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
            if(!$user->organization_accepted || !$user->isPaied) {
                $user->organization_accepted = true;
                $user->isPaied = 1;
                $user->update();
                $this->sendMail($congress,$user);
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
        $user->isPaied = 1;
        $user->update();
        $organization->congress_organization->update();
        $congress = $this->congressServices->getCongressById($organization->congress_organization->congress_id);
        $this->sendMail($congress,$user);
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

        $link = Utils::baseUrlWEB . "/#/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
        if ($mailtype = $this->congressServices->getMailType('subvention')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null,$organization), $user, $congress, $mail->object, null,
                    $link);
            }
        }

        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null,null), $user, $congress, $mail->object, $fileAttached,
                    $link);
            }
        }
    }

}
