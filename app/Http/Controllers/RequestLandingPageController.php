<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\LandingPageServices;
use App\Services\MailServices;
use Illuminate\Support\Facades\Log;

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
        if (!$congress = $this->congressServices->getCongressDetailsById($congress_id)) {
            return response()->json(['message' => 'congress not found'], 404);
        }

        $landingPage=$this->landingPageServices->addRequestLandingPage($request, $congress_id, $admin->admin_id);
        return response()->json($landingPage, 200);
    }
    public function getLandingPages()
    {
        $landingPages = $this->landingPageServices->getLandingPages();
        return response()->json($landingPages, 200);
    }
    public function getLandingPagewithCongressId($congress_id)
    {
        $landingPage = $this->landingPageServices->getLandingPagewithCongressId($congress_id);
        return response()->json($landingPage, 200);
    }
    public function getOneLandingPage($request_landing_page_id)
    {
        $landingPage = $this->landingPageServices->getOneLandingPage($request_landing_page_id);
        return response()->json($landingPage, 200);
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
          $mailTypeAdmin = $this->mailServices->getMailTypeAdmin('accept_landing_page_demand');
            $linkBackOffice = $landingPage->dns;
        } else if ($status == -1) {
            $linkBackOffice = "";
         $mailTypeAdmin = $this->mailServices->getMailTypeAdmin('refuse_landing_page_demand');
        }
        if($mailTypeAdmin){
        $mailAdmin = $this->mailServices->getMailAdmin($mailTypeAdmin->mail_type_admin_id);
        $this->mailServices->sendMAil($this->adminServices->renderMail($mailAdmin->template,  $admin, null, null, $linkBackOffice), $admin->email, null,  $mailAdmin->object, false, null, $landingPage->admin->email, null);
        }
        return response()->json($landingPage, 200);
    }
    public function getConfigLandingPage($congress_id)
    {
      
        $config_landing_page = $this->congressServices->getConfigLandingPageById($congress_id);
        $configLocation = $this->congressServices->getConfigLocationByCongressId($congress_id);
        return response()->json(['config_landing_page' => $config_landing_page, 'configLocation' => $configLocation], 200);
    }
    public function getLandingPageSpeakers($congress_id)
    {
        
        $speakers = $this->congressServices->getLandingPageSpeakers($congress_id);
        return response()->json($speakers, 200);
    }

    public function getLandingPagewithDnsName(Request $request)
    {
        $dns = $request->query('nameDns');
        $landingPage = $this->landingPageServices->getLandingPagewithDnsName($dns);
        return response()->json($landingPage, 200);
    }
}
