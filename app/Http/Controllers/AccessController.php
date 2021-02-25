<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAccess;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\NotificationServices;
use App\Services\ResourcesServices;
use App\Services\RoomServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessController extends Controller
{

    protected $accessServices;
    protected $adminServices;
    protected $userServices;
    protected $congressServices;
    protected $resourcesServices;
    protected $roomServices;
    protected $notificationServices;


    function __construct(AccessServices $accessServices,
                         AdminServices $adminServices,
                         UserServices $userServices,
                         CongressServices $congressServices,
                         ResourcesServices $resourcesServices,
                         RoomServices $roomServices,
                         NotificationServices $notificationServices)
    {
        $this->accessServices = $accessServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->resourcesServices = $resourcesServices;
        $this->roomServices = $roomServices;
        $this->notificationServices = $notificationServices;
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


    public function addAccess(Request $request, $congress_id)
    {
        if (!$request->has(['name', 'start_date', 'end_date', 'access_type_id']))
            return response()->json(['response' => 'invalid request',
                'required fields' => ['name', 'start_date', 'end_date', 'access_type_id']], 400);
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(['response' => 'congress not found'], 404);

        if (!$admin = $this->adminServices->retrieveAdminFromToken())
            return response()->json(['response' => 'admin not found'], 404);

        $access = $this->accessServices->addAccess($congress_id, $request);
        $token_jitsi = $this->roomServices->createToken($admin->email, 'eventizer_room_' . $congress_id . $access->access_id, true, $admin->name);
        $jitsi_moderator = $this->roomServices->createToken('moderator@eventizer.io', 'eventizer_room_' . $congress_id . $access->access_id, true, "Moderator");
        $jitsi_participant = $this->roomServices->createToken('participant@eventizer.io', 'eventizer_room_' . $congress_id . $access->access_id, false, "Participant");
        $access->token_jitsi_moderator = $jitsi_moderator;
        $access->token_jitsi_participant = $jitsi_participant;
        $access->token_jitsi = $token_jitsi;
        $access->update();

        if ($request->has('chair_ids') && count($request->input('chair_ids'))) {
            $this->accessServices->addChairs($access, $request->input('chair_ids'));
        }

        if ($request->has('speaker_ids') && count($request->input('speaker_ids'))) {
            $this->accessServices->addSpeakers($access, $request->input('speaker_ids'));
        }

        if ($request->has('resource_ids') && count($request->input('resource_ids'))) {
            $this->resourcesServices->addResources($access, $request->input('resource_ids'));
        }

        if ($request->has('sub_accesses') && count($request->input('sub_accesses'))) {
            $this->accessServices->addSubAccesses($access, $request->input('sub_accesses'));
        }

        if ($access->show_in_register == 1 || $access->packless == 1) {
            $users = $this->userServices->getUsersByCongress($congress_id, [5, 6, 7, 8]);
        } else {
            $users = $this->userServices->getUsersByCongress($congress_id);
        }

        $this->userServices->affectAccessToUsers($access, $users);

        return response()->json(['message' => 'add access success']);
    }

    public function getAccessById($access_id)
    {
        return $this->accessServices->getAccessById($access_id);
    }

    public function getByCongressId($congressId)
    {
        return $this->accessServices->getByCongressId($congressId);
    }

    public function deleteAccess($access_id)
    {
        $this->accessServices->deleteAccess($access_id);
        return response()->json(['message' => 'success'], 200);
    }

    public function editAccess(Request $request, $access_id)
    {
        if (!$access = $this->accessServices->getAccessById($access_id))
            return response()->json(['message' => 'access not found'], 404);

        $access = $this->accessServices->editAccess($access, $request);

        if ($request->has('is_recorder')) {
            $this->accessServices->editVideoUrl($access, $request->input('is_recorder'));
        }

        if ($request->has('chair_ids') && count($request->input('chair_ids'))) {
            $this->accessServices->editChairs($access_id, $request->input('chair_ids'));
        } else $this->accessServices->removeAllChairs($access_id);

        if ($request->has('speaker_ids') && count($request->input('speaker_ids'))) {
            $this->accessServices->editSpeakers($access_id, $request->input('speaker_ids'));
        } else $this->accessServices->removeAllSpeakers($access_id);

        if ($request->has('resource_ids') && count($request->input('resource_ids'))) {
            $this->resourcesServices->editAccessResources($access_id, $request->input('resource_ids'));
        } else $this->resourcesServices->removeAllResources($access_id);


        if ($request->has('sub_accesses') && count($request->input('sub_accesses'))) {
            $this->accessServices->editSubAccesses($access, $request->input('sub_accesses'));
        } else $this->accessServices->deleteAllSubAccesses($access_id);

        $this->notificationServices->sendNotificationToCongress('Changement du programme: ' . $access->name
            , $access->congress_id);

        return $this->accessServices->getAccessById($access->access_id);
    }

    public function getAccessTypes()
    {
        return $this->accessServices->getAccessTypes();
    }

    public function getAccessTopics()
    {
        return $this->accessServices->getAccessTopics();
    }

    public function getMainByCongressId($congress_id)
    {
        return $this->accessServices->getMainByCongressId($congress_id);
    }

    public function verifyPrivilegeByAccess($accessId, $userId)
    {

        $chairPerson = $this->accessServices->getChairAccessByAccessAndUser($accessId, $userId);

        if ($chairPerson) {
            return response()->json(['privilegeId' => 5]);
        }

        $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($accessId, $userId);
        if ($speakerPerson) {
            return response()->json(['privilegeId' => 8]);
        }

        return response()->json(['message' => 'not found'], 404);
    }

    function updateTokensJitsi($congressId)
    {
        $accesses = $this->accessServices->getAccesssByCongressId($congressId);

        foreach ($accesses as $access) {
                $jitsi_moderator = $this->roomServices->createToken('moderator@eventizer.io', 'eventizer_room_' . $congressId . $access->access_id, true, "Moderator");
                $jitsi_participant = $this->roomServices->createToken('participant@eventizer.io', 'eventizer_room_' . $congressId . $access->access_id, false, "Participant");
                $access->token_jitsi_moderator = $jitsi_moderator;
                $access->token_jitsi_participant = $jitsi_participant;
                $access->update();

        }
    }

    public function editAccessStatus($congress_id, Request $request)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        
        $all = $request->query('all', false);
        $status = $request->query('status', 1);
        $access_id = $request->query('accessId', null);

        if ($all=='true') {
            $this->accessServices->editAllAccessesStatus($congress_id, $status);
        } else {
            $this->accessServices->editAccessStatus($access_id, $status);
        }
        return response()->json($this->accessServices->getByCongressId($congress_id));
    }
    
    public function getScoresByCongressId($congress_id, Request $request)
    {
        if (!$this->congressServices->getById($congress_id)) {
            return response()->json(['response' => 'congress not found'], 404);
        }
        $access_id = $request->query('access_id');
        if ($access_id && $access_id != 'null') {
            $access = $this->accessServices->getAccessById($access_id);
            if (!$access || $access->access_type_id !=4)
            {
            return response()->json(['response' => 'bad request'], 400);
            }
            $access_game = $this->accessServices->getScoresByAccess($access_id);
        } else {
            $accesses = $this->accessServices->getGamesAccessesByCongress($congress_id);
            $access_game = $this->accessServices->getScoresByCongress($accesses);
        }
        return response()->json($access_game, 200);
    }

    public function saveScoreGame($congress_id, Request $request)
    {
        if (!$this->congressServices->getById($congress_id)) {
            return response()->json(['response' => 'congress not found'], 404);
        }

        if(!$request->has(['user_id', 'score'])){
            return response()->json(['response' => 'bad request (required user_id, score)']);
        }

        if (!$request->has('name')) {
            return response()->json(['response' => 'missing access name'], 400);
        }
        $name = $request->query('name');
        $access = $this->accessServices->getAccessByName($name);
        if (!$access || $access->access_type_id !=4)
        {
            return response()->json(['response' => 'Access not found or not type Game'], 400);
        }
        $accessGame = $this->accessServices->saveScoreGame($access->access_id, $request);
        return response()->json($accessGame, 200);
    }

    public function getScoresByCongressPeaksource($congress_id, Request $request)
    {
        if (!$this->congressServices->getById($congress_id)) {
            return response()->json(['response' => 'congress not found'], 404);
        }
        $name = $request->query('name');
        if ($name && !is_null($name) && $name !='null') {
            $access = $this->accessServices->getAccessByName($name);
            if (!$access || $access->access_type_id !=4)
            {
            return response()->json(['response' => 'bad request'], 400);
            }
            $access_game = $this->accessServices->getScoresByAccess($access->access_id);
        } else {
            $accesses = $this->accessServices->getGamesAccessesByCongress($congress_id);
            $access_game = $this->accessServices->getScoresByCongress($accesses);
        }
        return response()->json($access_game, 200);
    }

}
