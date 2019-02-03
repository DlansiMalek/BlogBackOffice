<?php

namespace App\Http\Controllers;


use App\Models\Mail;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\OrganizationServices;
use App\Services\UserServices;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{

    protected $organizationServices;
    protected $congressServices;
    protected $adminServices;
    protected $userServices;


    function __construct(OrganizationServices $organizationServices,
                         CongressServices $congressServices,
                         AdminServices $adminServices,
                         UserServices $userServices)
    {
        $this->organizationServices = $organizationServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
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
            $this->organizationServices->sendMail($this->congressServices->renderMail($mail->template, $congress, null, null), $congress, $mail->object, $organization["organization"]->email);

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
        foreach ($organization->users as $user) {
            $organization->congress_organization->montant += $user->price;
            $user->organization_accepted = true;
            $user->update();
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
        $user->update();
        $organization->congress_organization->update();
        return $this->organizationServices->getOrganizationById($organization->organization_id);
    }

}
