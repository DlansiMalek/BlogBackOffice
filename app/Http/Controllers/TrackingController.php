<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:36
 */

namespace App\Http\Controllers;

use App\Services\CongressServices;
use App\Services\TrackingServices;
use App\Services\UserServices;


class TrackingController extends Controller
{

    protected $trackingServices;
    protected $congressServices;
    protected $userServices;

    function __construct(TrackingServices $trackingServices, CongressServices $congressServices, UserServices $userServices)
    {
        $this->trackingServices = $trackingServices;
        $this->congressServices = $congressServices;
        $this->userServices = $userServices;
    }


    function migrateUsers($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        $users = $this->userServices->getAllUsersByCongress($congressId, null, 0);

        foreach ($users as $user) {
            $this->trackingServices->sendUserInfo($congressId, $congress->form_inputs, $user);
        }

        return response()->json(['message' => 'migration done'], 200);
    }


}
