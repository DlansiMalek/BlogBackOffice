<?php

namespace App\Http\Controllers;


use App\Models\User_Access;
use App\Services\AccessServices;
use App\Services\UserServices;
use Illuminate\Http\Request;

class AccessController extends Controller
{

    protected $accessServices;
    protected $userServices;


    function __construct(AccessServices $accessServices, UserServices $userServices)
    {
        $this->accessServices = $accessServices;
        $this->userServices = $userServices;
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

    public function grantAccessByCountry($countryId, Request $request)
    {
        $congressId = $request->input('congressId');

        $users = $this->userServices->getUsersByContry($congressId, $countryId);

        $accesss = $this->accessServices->getAllAccessByCongress($congressId);
        foreach ($users as $user) {
            User_Access::where('user_id', '=', $user->user_id)
                ->delete();
            foreach ($accesss as $access) {
                $userAccess = new User_Access();
                $userAccess->user_id = $user->user_id;
                $userAccess->access_id = $access->access_id;
                $userAccess->save();
            }
        }
        return response()->json(['message' => 'success', 'user_number' => sizeof($users)]);
    }

    public function grantAccessByParticipantType($participantTypeId, Request $request)
    {
        $congressId = $request->input('congressId');

        $users = $this->userServices->getUsersByParticipantTypeId($congressId, $participantTypeId);

        $accesss = $this->accessServices->getAllAccessByCongress($congressId);
        foreach ($users as $user) {
            User_Access::where('user_id', '=', $user->user_id)
                ->delete();
            foreach ($accesss as $access) {
                $userAccess = new User_Access();
                $userAccess->user_id = $user->user_id;
                $userAccess->access_id = $access->access_id;
                $userAccess->save();
            }
        }
        return response()->json(['message' => 'success', 'user_number' => sizeof($users)]);
    }

}
