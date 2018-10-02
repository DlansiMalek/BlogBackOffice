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


}
