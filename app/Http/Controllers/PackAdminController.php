<?php

namespace App\Http\Controllers;


use App\Models\PackAdmin;
use App\Services\PackAdminServices;
use Illuminate\Http\Request;

class PackAdminController extends Controller
{
    //
    protected $packadminservice ;

    function __construct(PackAdminServices $packservice)
    {
        $this->packadminservice = $packservice;

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
        if (!$request->has(['name', 'type'])) {
            return response()->json(['response' => 'invalid request',
                'content' => ['name', 'type', 'capacity',
                    'price', 'nbr_days', 'nbr_events']], 400);
        }
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

    /*@todo check if module_id exists or not */
        public function addmoduletoPack(Request $request,$pack_id){
         if (!$request->has('module_id')){
        return response()->json(['response' => 'invalid request'], 400);
        }
        $pack = $this->$this->packadminservice->getPackById($pack_id);
        if (!$pack) {
            return response()->json(['response' => 'pack not found'], 404);
        }
        else
            $this->packadminservice->addModuleToPack($request ,$pack);
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

}
