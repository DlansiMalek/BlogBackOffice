<?php

namespace App\Http\Controllers;


use App\Models\Access;
use App\Models\UserAccess;
use App\Resource;
use App\Services\AccessServices;
use App\Services\CongressServices;
use App\Services\ResourcesServices;
use App\Services\UserServices;
use Illuminate\Http\Request;

class AccessController extends Controller
{

    protected $accessServices;
    protected $userServices;
    protected $congressServices;
    protected $resourcesServices;


    function __construct(AccessServices $accessServices,
                         UserServices $userServices,
                         CongressServices $congressServices,
                         ResourcesServices $resourcesServices)
    {
        $this->accessServices = $accessServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->resourcesServices = $resourcesServices;
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

    public function addAccess(Request $request, $congress_id)
    {
        if (!$request->has(['name', 'start_date', 'end_date', 'access_type_id']))
            return response()->json(['response' => 'invalid request',
                'required fields' => ['name', 'start_date', 'end_date', 'access_type_id']], 400);
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(['response' => 'congress not found'], 404);

        $access = $this->accessServices->addAccess($congress_id, $request);

        if ($request->has('chair_ids') && count($request->input('chair_ids'))){
            $this->accessServices->addChairs($access, $request->input('chair_ids'));
        }

        if ($request->has('speaker_ids') && count($request->input('chair_ids'))){
            $this->accessServices->addSpeakers($access, $request->input('speaker_ids'));
        }

        if ($request->has('resource_ids') && count($request->input('resource_ids'))){
            $this->resourcesServices->addResources($access, $request->input('resource_ids'));
        }

        if ($request->has('sub_accesses') && count($request->input('sub_accesses'))){
            $this->accessServices->addSubAccesses($access, $request->input('sub_accesses'));
        }

        return $this->accessServices->getAccessById($access->access_id);

    }

}
