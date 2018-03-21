<?php

namespace App\Http\Controllers;


use App\Models\Access;
use App\Services\AccessServices;
use App\Services\AddInfoServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use Illuminate\Http\Request;


class CongressController extends Controller
{

    protected $congressServices;
    protected $adminServices;
    protected $addInfoServices;
    protected $accessServices;


    function __construct(CongressServices $congressServices, AdminServices $adminServices,
                         AddInfoServices $addInfoServices,
                         AccessServices $accessServices)
    {
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->addInfoServices = $addInfoServices;
        $this->accessServices = $accessServices;
    }


    public function addCongress(Request $request)
    {
        $admin = $this->adminServices->retrieveAdminFromToken();

        $congress = $this->congressServices->addCongress($request->input("name"), $request->input("date"), $admin->admin_id);
        $this->adminServices->addResponsibleCongress($request->input("responsibleIds"), $congress->congress_id);
        $this->addInfoServices->addInfoToCongress($congress->congress_id, $request->input("addInfoIds"));
        $this->accessServices->addAccessToCongress($congress->congress_id, $request->input("accesss"));


        return response()->json(["message" => "add congress sucess", "data" => $this->congressServices->getCongressById($congress->congress_id)]);


    }

}
