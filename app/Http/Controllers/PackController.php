<?php

namespace App\Http\Controllers;


use App\Services\CongressServices;
use App\Services\PackServices;
use Illuminate\Http\Request;

class PackController extends Controller
{

    protected $packServices;
    protected $congressServices;


    function __construct(PackServices $packServices,
                         CongressServices $congressServices)
    {
        $this->packServices = $packServices;
        $this->congressServices = $congressServices;
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

        return $this->packServices->addPack(
                $congressId,
                $request->input('label'),
                $request->input('description'),
                $request->input('price'),
                $request->input('accessIds')
        );
    }


}
