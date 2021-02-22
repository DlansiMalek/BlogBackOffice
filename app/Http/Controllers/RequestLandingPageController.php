<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\LandingPageServices;
use App\Services\MailServices;

class RequestLandingPageController extends Controller
{
    protected $adminServices;
    protected $congressServices;
    protected $landingPageServices;
    protected $mailServices;

    function __construct(
        MailServices $mailServices,
        AdminServices $adminServices,
        CongressServices $congressServices,
        LandingPageServices $landingPageServices
    ) {

        $this->adminServices = $adminServices;
        $this->congressServices = $congressServices;
        $this->landingPageServices = $landingPageServices;
        $this->mailServices = $mailServices;
    }
    public function addRequestLandingPage($congress_id, Request $request)
    {

        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['message' => 'congress not found'], 404);
        }

        $this->landingPageServices->addRequestLandingPage($request, $congress_id, $admin->admin_id);
    }
    public function getLandingPages()
    {
        $landingPages = $this->landingPageServices->getLandingPages();
        return response()->json($landingPages, 200);
    }
    public function getLandingPagewithcongress_id($congress_id)
    {
        $landingPage = $this->landingPageServices->getLandingPagewithcongress_id($congress_id);
        return response()->json($landingPage, 200);
    }
    public function getOneLandingPage($request_landing_page_id)
    {
        return  $this->landingPageServices->getOneLandingPage($request_landing_page_id);
    }
    public function upadteStatusLandingPage(Request $request, $request_landing_page_id)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $landingPage = $this->landingPageServices->getOneLandingPage($request_landing_page_id);
        $status = $request->input('status');
        $this->landingPageServices->upadteStatusLandingPage($landingPage, $status);
        if ($status == 1) {
            if (!$mailTypeAdmin = $this->mailServices->getMailTypeAdmin('Acceptation du votre demande Landing Page')) {
                return response()->json(['message' => 'Mail type not found'], 400);
            }
            $linkBackOffice = $landingPage->dns;
        } else if ($status == -1) {
            $linkBackOffice = "";
            if (!$mailTypeAdmin = $this->mailServices->getMailTypeAdmin('votre demande Landing Page est Refusé')) {
                return response()->json(['message' => 'Mail type not found'], 400);
            }
        }
        $mailAdmin = $this->mailServices->getMailAdmin($mailTypeAdmin->mail_type_admin_id);

        if (!$mailAdmin) {
            return response()->json(['message' => 'Mail not found'], 400);
        }

        $this->adminServices->sendMAil($this->adminServices->renderMail($mailAdmin->template,  $landingPage->admin, null, null, $linkBackOffice), null, $mailAdmin->object,  $landingPage->admin, null, null);
        return response()->json($landingPage, 200);
    }
}
