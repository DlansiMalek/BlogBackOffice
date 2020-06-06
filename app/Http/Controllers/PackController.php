<?php

namespace App\Http\Controllers;

use App\Services\AccessServices;
use App\Services\CongressServices;
use App\Services\PackServices;
use Illuminate\Http\Request;

class PackController extends Controller
{

    protected $packServices;
    protected $congressServices;
    protected $accessServices;

    function __construct(PackServices $packServices,
                         CongressServices $congressServices,
                         AccessServices $accessServices)
    {
        $this->packServices = $packServices;
        $this->congressServices = $congressServices;
        $this->accessServices = $accessServices;
    }


    public function getAllPackByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        return response()->json($this->packServices->getAllPackByCongress($congressId));
    }

    public function addPack($congressId, Request $request) {
        
        if (!$congress = $this->congressServices->getCongressById($congressId)){
            return response()->json(['response' => 'No congress found'],400);
        }

        $pack = $this->packServices->addPack(
                $congressId,
                $request->input('label'),
                $request->input('description'),
                $request->input('price'),
                $request->input('accessIds')
        );
        $acesss = $this->accessServices->getByCongressId($congressId);
        $this->accessServices->ChangeAccessPacklessZeroToOne(
            $request->input('accessIds'),
            $acesss
        );
        return response()->json(['responsse' => 'packed added'],200);
    }

    public function deletePack($packId)
    {
        $pack = $this->packServices->getPackById($packId);
        if (!$pack) {
            return response()->json(['response' => 'pack not found'], 404);
        } elseif ($pack) {
            if ($this->packServices->checkIfHasRelation($packId))
            {
                $pack->delete();
            }
            else return response()->json(['response' => 'error ! pack already assigned to a participant'], 404);
        }
        return response()->json(['response' => 'pack deleted'], 202);    }
}