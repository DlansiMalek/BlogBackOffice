<?php

namespace App\Services;

use App\Models\RequestLandingPage;

class LandingPageServices
{
    public function __construct()
    {
    }

    public function addRequestLandingPage($LandingPageRequest, $congress_id, $admin_id)
    {
        if (!$LandingPage = $this->getLandingPagewithCongressIdAndAdminID($congress_id, $admin_id)) {
            $LandingPage = new RequestLandingPage();
            $exists = false;

        }
        $LandingPage->dns = $LandingPageRequest->input('dns');
        $LandingPage->congress_id = $congress_id;
        $LandingPage->admin_id = $admin_id;
        !$exists ? $LandingPage->save():  $LandingPage->update();
        return $LandingPage;
    }
    public function getLandingPages()
    {
        return RequestLandingPage::with(['congress', 'admin'])->get();
    }

    public function getLandingPagewithCongressId($congress_id)
    {
        return RequestLandingPage::where('congress_id', '=', $congress_id)->where('status', '!=', -1)->first();
    }
    public function getLandingPagewithCongressIdAndAdminID($congress_id, $admin_id)
    {
        return RequestLandingPage::where('congress_id', '=', $congress_id)->where('admin_id', '=', $admin_id)->where('status', '!=', -1)->first();
    }
    public function upadteStatusLandingPage($landingPage, $status)
    {
        $landingPage->status = $status;
        $landingPage->update();
        return $landingPage;
    }

    public function getOneLandingPage($request_landing_page_id)
    {
        return RequestLandingPage::where('request_landing_page_id', '=', $request_landing_page_id)->with(['admin'])->first();
    }
}
