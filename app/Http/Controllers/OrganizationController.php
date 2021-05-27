<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OrganizationServices;
use App\Services\PaymentServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\UrlUtils;
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


    function __construct(OrganizationServices $organizationServices,
                         CongressServices $congressServices,
                         AdminServices $adminServices,
                         UserServices $userServices,
                         SharedServices $sharedServices,
                         PaymentServices $paymentServices,
                         AccessServices $accessServices,
                         MailServices $mailServices)
    {
        $this->organizationServices = $organizationServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
        $this->paymentServices = $paymentServices;
        $this->accessServices = $accessServices;
        $this->mailServices = $mailServices;
    }

    public function addOrganization($congress_id, Request $request)
    {
        $privilegeId = 7; //Privilegg Organisme;
        if (!$request->has(['name'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['email', 'nom']], 404);
        }

        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }
		
		$admin_id =$request->input("admin_id");
        $admin = $this->adminServices->getAdminById($admin_id);
        $organization = $this->organizationServices->getOrganizationByName($request->input("name"));
        if (!$organization) {
            $organization = $this->organizationServices->addOrganization($request);
        } else {
            if ($this->organizationServices->getOrganizationByCongressIdAndOrgId($congress_id, $organization->organization_id)) {
                return response()->json(["message" => "organization already exists in this congress"], 401);
            }
        }

        $this->adminServices->addAdminCongress($admin_id, $congress_id, $privilegeId);

        $this->organizationServices->affectOrganizationToCongress($congress_id, $organization->organization_id, $request->input('is_sponsor'), $request->input('banner'), $request->input('resource_id'), $request->input("admin_id"));

        if ($mailtype = $this->congressServices->getMailType('organization')) {
            if (!$mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Coordonnées pour l'accès à la plateforme VayeCongress";
            }

            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $privilegeId);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic($badge,
                    $organization->name,
                    $admin->passwordDecrypt,
                    $privilegeId);
            }
            $mail->template = $mail->template . "<br>Votre Email pour accéder à la plateforme <a href='https://organizer.eventizer.io'>Eventizer</a>: " . $admin->email;
            $mail->template = $mail->template . "<br>Votre mot de passe pour accéder à la plateforme <a href='https://organizer.eventizer.io'>Eventizer</a>: " . $admin->passwordDecrypt;

            $this->adminServices->sendMail($this->congressServices->renderMail($mail->template, $congress, null, null, $organization, null), $congress, $mail->object, $admin, $fileAttached);
        }

        return response()->json($this->organizationServices->getOrganizationById($organization->organization_id));
    }

    function editOrganization (Request $request, $organization_id) {
        $oldOrg = $this->organizationServices->getOrganizationById($organization_id);
        $email = $request->has("email") ? $request->input("email") : $request->input("name") . '@eventizer.io';
        $admin = $this->adminServices->getAdminById($request->input("admin")["admin_id"]);
        $admin->email = $email;
        $admin->name = $request->input("name");
        $admin->mobile = $request->input("mobile");
        $this->adminServices->editPersonnel($admin);
        $this->organizationServices->editOrganization(
         $oldOrg,
         $request
      );
      $organization = $this->organizationServices->getOrganizationById($organization_id);
      return response()->json($organization,200);
 }


   function deleteOrganization($congress_id, $organization_id)
   {  
       if (!$organization = $this->organizationServices->getOrganizationById($organization_id))
            return response()->json('no organization found' ,404);
        $congressOrganization = $this->organizationServices->getCongressOrganization($congress_id, $organization_id);
        $this->organizationServices->deleteCongressOrganization($congressOrganization);
        $this->organizationServices->deleteOrganization($organization);
        return response()->json(['response' => 'organization deleted'],200);
      }

    public function getCongressOrganizations($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(["message" => "congress not found"], 404);

        $organizations = $this->organizationServices->getOrganizationsByCongressId($congress_id);

        return response()->json($organizations);
    }

    public function getOrganizmeByCongress(Request $request,$congressId) {
        $isLogoPosition = $request->query('logo');
        if (!$this->congressServices->getCongressById($congressId)) {
            return response()->json('no congress found',404);
        }
        return  $this->organizationServices->getOrganizmeByCongressId($congressId,$isLogoPosition);


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

    public function getOrganizationById($organization_id)
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
        $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $user->privilege_id);
        $badgeIdGenerator = $badge['badge_id_generator'];

        $fileAttached = false;
        $fileName = "badge.png";
        if ($badgeIdGenerator != null) {
            $fileAttached = $this->sharedServices->saveBadgeInPublic($badge,
                $user,
                $user->qr_code,
                $user->privilege_id);
        }


        if ($mailtype = $this->congressServices->getMailType('subvention')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, $organization, null), $user, $congress, $mail->object, null);
            }
        }

        if ($mailtype = $this->congressServices->getMailType('confirmation')) {
            $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice), $user, $congress, $mail->object, $fileAttached, null, null ,$fileName);
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


    function getAllUserByOrganizationId($organizationId, $congressId)
    {
        return response()->json($this->organizationServices->getAllUserByOrganizationId($organizationId, $congressId));
    }
    
    public function getSponsorsByCongressId($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(["message" => "congress not found"], 404);

        $organizations = $this->organizationServices->getSponsorsByCongressId($congress_id);

        return response()->json($organizations);
    }
}
