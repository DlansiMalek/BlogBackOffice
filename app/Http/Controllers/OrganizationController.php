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

    public
    function getCongressOrganizations($congress_id)
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

}
