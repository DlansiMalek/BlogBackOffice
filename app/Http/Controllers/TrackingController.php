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
use Illuminate\Support\Facades\Log;


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

    function migrateTracking($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        //TODO get all users with trackings array
        $users = $this->userServices->getUsersTracking($congressId, [1, 2, 3, 4]);

        //TODO for each user
        foreach ($users as $user) {
            //TODO Groupby login/logout congress
            $mvCongress = $this->trackingServices->groupByActionIds($user->tracking, [1, 2]);
            //TODO Groupby entry/leave access
            $mvAccess = $this->trackingServices->groupByActionIds($user->tracking, [3, 4], 'ACCESS');
            //TODO Groupby entry/leave stand
            $mvStand = $this->trackingServices->groupByActionIds($user->tracking, [3, 4], 'STAND');


            $this->trackingServices->sendTrackingPair('LOGIN_LOGOUT', $mvCongress, 1, 2);
            $this->trackingServices->sendTrackingPair('ENTRY_LEAVE', $mvAccess, 3, 4);
            $this->trackingServices->sendTrackingPair('ENTRY_LEAVE', $mvStand, 3, 4);
        }


        //TODO Send normal tracking to others
        $users = $this->userServices->getUsersTracking($congressId, [5, 6, 7, 8, 9, 10]);

        //TODO for each user
        foreach ($users as $user) {
            $this->trackingServices->sendTrackingNormal($user->tracking);
        }

        return response()->json(['message' => 'send tracking success'], 200);
    }


}
