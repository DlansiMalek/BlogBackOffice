<?php

namespace App\Http\Controllers;


use App\Services\AccessServices;
use Illuminate\Http\Request;

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

    function startAccessById(Request $request)
    {
        $accessId = $request->input('access_id');

        if (!$access = $this->accessServices->getById($accessId)) {
            return response()->json(['error' => 'access not found'], 404);
        }

        //DENTAIRE SHIT
        if ($accessId == 8) {
            $accessShit = $this->accessServices->getById(25);
            if ($accessShit->start_date == null) {
                $accessShit->start_date = date('Y-m-d H:i:s');
                $accessShit->update();
            }
        }

        if ($access->start_date == null) {
            $access->start_date = date('Y-m-d H:i:s');
            $access->update();
        }

        return response()->json($access);

    }


}
