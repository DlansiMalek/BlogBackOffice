<?php

namespace App\Http\Controllers;


use App\Services\AccessServices;

class AccessController extends Controller
{

    protected $accessServices;


    function __construct(AccessServices $accessServices)
    {
        $this->accessServices = $accessServices;
    }
    

    function getAllAccessByCongress($congressId)
    {

        return response()->json($this->accessServices->getAllAccessByCongress($congressId));

    }


}
