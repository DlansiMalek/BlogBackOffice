<?php

namespace App\Http\Controllers;


use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\OrganizationServices;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{

    protected $organizationServices;
    protected $congressServices;
    protected $adminServices;


    function __construct(OrganizationServices $organizationServices,
                         CongressServices $congressServices,
                         AdminServices $adminServices)
    {
        $this->organizationServices = $organizationServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
    }

    public function addOrganization($congress_id, Request $request)
    {
        if (!$request->has(['email', 'name'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['email', 'nom']], 404);
        }

        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        if (!$organization = $this->organizationServices->addOrganization($request, $congress_id, $admin->admin_id)) {
            return response()->json(["message" => "error adding organization"], 404);
        }

        return $organization;


    }

    public function getCongressOrganizations($congress_id){
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(["message" => "congress not found"], 404);
        return $congress->organizations? $congress->organizations:[];
    }

}
