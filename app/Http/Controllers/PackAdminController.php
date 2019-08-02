<?php

namespace App\Http\Controllers;


use App\Models\PackAdmin;
use App\Services\PackAdminServices;
use App\Services\ModuleServices;
use Illuminate\Http\Request;

class PackAdminController extends Controller
{
    //
    protected $packadminservice ;
    protected $moduleservice ;

    function __construct(PackAdminServices $packservice,ModuleServices $moduleservice)
    {
        $this->packadminservice = $packservice;
        $this->moduleservice = $moduleservice;
    }
    public function index()
    {
        return $this->packadminservice->getAllPacks();
    }
    public function getPackById($pack_id)
    {
        $pack = $this->packadminservice->getPackById($pack_id);
        if (!$pack) {
            return response()->json(['response' => 'pack not found'], 404);
        }

        return response()->json($pack, 200);
    }

    public function update(Request $request, $pack_id)
    {
       /* if (!$request->has(['name', 'type'])) {
            return response()->json(['response' => 'invalid request',
                'content' => ['name', 'type', 'capacity',
                    'price', 'nbr_days', 'nbr_events']], 400);
        }*/
        $pack = $this->packadminservice->getPackById($pack_id);
        if (!$pack) {
            return response()->json(['response' => 'pack not found'], 404);
        }
        return response()->json($this->packadminservice->updatePack($request, $pack), 202);
    }

    public function delete($packId)
    {
        $pack = $this->packadminservice->getPackById( $packId);
        if (!$pack) {
            return response()->json(['response' => 'pack not found'], 404);
        }
        elseif ($pack) {
            $pack->delete();
        }
        return response()->json(['response' => 'pack deleted'], 202);
    }

        public function addmoduletoPack($pack_id , $module_id){
        $pack = $this->packadminservice->getPackById($pack_id);
        $module = $this->moduleservice->getModuleById($module_id);
        if (!$pack) {
            return response()->json(['response' => 'pack not found'], 404);
        }
        elseif (!$module){return response()->json(['response' => 'module not found'], 404);}
        else
            $this->packadminservice->addModuleToPack($module_id ,$pack);
            return response()->json(['response' => 'added module to pack '], 202);
    }

    public function store(Request $request)
    {
        if (!$request->has(['name', 'type','capacity'])) {
            return response()->json(['response' => 'invalid request',
                'content' => ['name', 'type', 'capacity',
                    'price', 'nbr_days', 'nbr_events']], 400);
        }
        $pack =  new PackAdmin();
        return response()->json($this->packadminservice->AddPack($request, $pack), 202);
        }

        public function getpackmodules ($pack_id){
            $pack = $this->packadminservice->getPackById( $pack_id);
            if (!$pack) {
                return response()->json(['response' => 'pack not found'], 404);
            }
            else
                return response()->json($this->packadminservice->getModulesFromPack($pack_id), 202);
        }

    public function getmodules(){
        return $this->moduleservice->getAllModules();
    }
}
