<?php

namespace App\Http\Controllers;


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
        $congress = $this->addFullCongress($request);


        return response()->json(["message" => "add congress sucess", "data" => $this->congressServices->getCongressById($congress->congress_id)]);


    }

    public function editCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["message" => "congress not found"], 404);
        }
        try {
            $congress->delete();
        } catch (\Exception $e) {
        }
        $this->addFullCongress($request);

        return response()->json(["message" => "edit congress success"]);
    }

    public function getCongressById($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }

        return response()->json($congress);
    }

    private function addFullCongress($request)
    {
        $admin = $this->adminServices->retrieveAdminFromToken();

        $congress = $this->congressServices->addCongress($request->input("name"), $request->input("date"), $admin->admin_id);
        $this->adminServices->addResponsibleCongress($request->input("responsibleIds"), $congress->congress_id);
        $this->addInfoServices->addInfoToCongress($congress->congress_id, $request->input("addInfoIds"));
        $this->accessServices->addAccessToCongress($congress->congress_id, $request->input("accesss"));

        return $congress;
    }

}
