<?php

namespace App\Http\Controllers;

use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\NotificationServices;
use App\Services\ResourcesServices;
use App\Services\RoomServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Http\Request;

class AccessController extends Controller
{

    protected $accessServices;
    protected $adminServices;
    protected $userServices;
    protected $congressServices;
    protected $resourcesServices;
    protected $roomServices;
    protected $notificationServices;

    public function __construct(AccessServices $accessServices,
        AdminServices $adminServices,
        UserServices $userServices,
        CongressServices $congressServices,
        ResourcesServices $resourcesServices,
        RoomServices $roomServices,
        NotificationServices $notificationServices) {
        $this->accessServices = $accessServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->resourcesServices = $resourcesServices;
        $this->roomServices = $roomServices;
        $this->notificationServices = $notificationServices;
    }

    public function startAccessById(Request $request)
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
        if (!$request->has(['name', 'start_date', 'end_date', 'access_type_id'])) {
            return response()->json(['response' => 'invalid request',
                'required fields' => ['name', 'start_date', 'end_date', 'access_type_id']], 400);
        }

        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'congress not found'], 404);
        }

        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['response' => 'admin not found'], 404);
        }

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
        $access = $this->accessServices->getAccessById($access->access_id);

        return response()->json($access);
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
        if (!$access = $this->accessServices->getAccessById($access_id)) {
            return response()->json(['message' => 'access not found'], 404);
        }

        $access = $this->accessServices->editAccess($access, $request);

        if ($request->has('is_recorder')) {
            $this->accessServices->editVideoUrl($access, $request->input('is_recorder'));
        }

        if ($request->has('chair_ids') && count($request->input('chair_ids'))) {
            $this->accessServices->editChairs($access_id, $request->input('chair_ids'));
        } else {
            $this->accessServices->removeAllChairs($access_id);
        }

        if ($request->has('speaker_ids') && count($request->input('speaker_ids'))) {
            $this->accessServices->editSpeakers($access_id, $request->input('speaker_ids'));
        } else {
            $this->accessServices->removeAllSpeakers($access_id);
        }

        if ($request->has('resource_ids') && count($request->input('resource_ids'))) {
            $this->resourcesServices->editAccessResources($access_id, $request->input('resource_ids'));
        } else {
            $this->resourcesServices->removeAllResources($access_id);
        }

        if ($request->has('sub_accesses') && count($request->input('sub_accesses'))) {
            $this->accessServices->editSubAccesses($access, $request->input('sub_accesses'));
        } else {
            $this->accessServices->deleteAllSubAccesses($access_id);
        }

        $this->notificationServices->sendNotificationToCongress('Changement du programme: ' . $access->name
            , $access->congress_id);

        $this->congressServices->deleteAllAllowedAccessByCongressId($access->congress_id, $access->access_id);

        if ($request->has('privileges') && count($request->input('privileges'))) {
            $this->congressServices->addAllAllowedAccessByCongressId($request->input('privileges'), $access->congress_id, $access->access_id);
        }
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
            return response()->json(['privilegeId' => config('privilege.Moderateur')]);
        }

        $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($accessId, $userId);
        if ($speakerPerson) {
            return response()->json(['privilegeId' => config('privilege.Conferencier_Orateur')]);
        }

        return response()->json(['message' => 'not found'], 404);
    }

    public function updateTokensJitsi($congressId)
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

        if ($all == 'true') {
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
            if (!$access || $access->access_type_id != 4) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $access_game = $this->accessServices->getScoresByAccess($congress_id, $access_id);
        } else {
            $accesses = $this->accessServices->getGamesAccessesByCongress($congress_id);
            $access_game = $this->accessServices->getScoresByCongress($congress_id, $accesses);
        }
        return response()->json($access_game, 200);
    }

    public function saveScoreGame($congress_id, Request $request)
    {
        if (!$this->congressServices->getById($congress_id)) {
            return response()->json(['response' => 'congress not found'], 404);
        }

        if (!$request->has(['user_id', 'score'])) {
            return response()->json(['response' => 'bad request (required user_id, score)']);
        }

        if (!$request->has('name')) {
            return response()->json(['response' => 'missing access name'], 400);
        }
        $name = $request->query('name');
        $access = $this->accessServices->getAccessByName($name);
        if (!$access || $access->access_type_id != 4) {
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
        if ($name && !is_null($name) && $name != 'null') {
            $access = $this->accessServices->getAccessByName($name);
            if (!$access || $access->access_type_id != 4) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $access_game = $this->accessServices->getScoresByAccess($congress_id, $access->access_id, true);
        } else {
            $accesses = $this->accessServices->getGamesAccessesByCongress($congress_id);
            $access_game = $this->accessServices->getScoresByCongress($congress_id, $accesses, true);
        }
        return response()->json($access_game, 200);
    }

    public function resetScore($access_id)
    {
        $access = $this->accessServices->getAccessById($access_id);
        if (!$access || $access->access_type_id != 4) {
            return response()->json(['response' => 'bad request'], 400);
        }
        $this->accessServices->resetScore($access_id);
        return response()->json('deleted successfully', 200);
    }

    public function uploadExcelAccess($congress_id, Request $request)
    {
        if (!$this->congressServices->getById($congress_id)) {
            return response()->json(['response' => 'congress not found'], 404);
        }
        if (!$request->has('accessTypeId')) {
            return response()->json(['response' => 'required access_type_id'], 400);
        }
        ini_set('max_execution_time', 500);
        $access_type_id = $request->input('accessTypeId');
        $accesses = $request->input("data");
        $errors = '';

        if (!$oldAccesses = $this->accessServices->getByCongressId($congress_id)) {
            $oldAccesses = [];
        }

        foreach ($oldAccesses as $old) {
            $found = false;
            foreach ($accesses as $access) {
                if ($access['email']) {
                    $user = $this->userServices->getUserByEmail($access['email'], $congress_id);
                    if ($user && count($user->user_congresses) > 0 && ($user->user_congresses[0]->privilege_id == config('privilege.Moderateur') || $user->user_congresses[0]->privilege_id == config('privilege.Conferencier_Orateur'))) {
                        $start_date = isset($access['start_date']) ? $access['start_date'] : null;
                        $end_date = isset($access['end_date']) ? $access['end_date'] : null;
                        $name = Utils::setAccessName($start_date, $end_date, $user->first_name . ' ' . $user->last_name);
                        if ($old->access_type_id == $access_type_id && $old->name == $name && $old->start_date == $start_date && $old->end_date == $end_date) {
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found && (count($old->speakers) > 0 || count($old->chairs) > 0) && count($old->packs) == 0) {
                $this->accessServices->deleteAccess($old->access_id);
            }
        }

        foreach ($accesses as $access) {
            $found = false;
            if ($access['email']) {
                $user = $this->userServices->getUserByEmail($access['email'], $congress_id);
                if ($user && count($user->user_congresses) > 0 && ($user->user_congresses[0]->privilege_id == config('privilege.Moderateur') || $user->user_congresses[0]->privilege_id == config('privilege.Conferencier_Orateur'))) {
                    $start_date = isset($access['start_date']) ? $access['start_date'] : null;
                    $end_date = isset($access['end_date']) ? $access['end_date'] : null;
                    $name = Utils::setAccessName($start_date, $end_date, $user->first_name . ' ' . $user->last_name);
                    foreach ($oldAccesses as $old) {
                        if ($old->access_type_id == $access_type_id && $old->name == $name && $old->start_date == $start_date && $old->end_date == $end_date) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $newAccess = $this->accessServices->addAccessFromExcel($start_date, $end_date, $access_type_id, $congress_id, $name);
                        $user->user_congresses[0]->privilege_id == config('privilege.Moderateur') ? $this->accessServices->addChair($newAccess->access_id, $user->user_id) : $this->accessServices->addSpeaker($newAccess->access_id, $user->user_id);
                    }
                } else {
                    $errors = $errors . ' ' . $access['line'];
                }
            }
        }
        $allAccesses = $this->accessServices->getByCongressId($congress_id);
        return response()->json(['accesses' => $allAccesses, 'errors' => $errors], 200);
    }

    public function getUserAccessesByCongressId($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $accesses = $this->accessServices->getUserAccessesByCongressId($congress_id, $user->user_id);
        return response()->json($accesses);
    }
    public function getAccessesByCongressIdPginantion($congressId, Request $request)
    {

        $offset = $request->query('offset', 0);
        $perPage = $request->query('perPage', 10);
        $search = $request->query('search', '');
        $date = $request->query('date', '');
        $startTime = $request->query('startTime', '');
        $endTime = $request->query('endTime', '');
        $isOnline = $request->query('isOnline', '');
        $myAccesses = $request->query('myAccesses', 0);
        if ($myAccesses == 1) {
            $user = $this->userServices->retrieveUserFromToken();
            if (!$user) {
                return response()->json(['response' => 'No user found'], 401);
            }
            $accesses = $this->accessServices->getAccessesByCongressIdPginantion($congressId, $offset, $perPage, $search, $date, $startTime, $endTime, $isOnline, $myAccesses, $user->user_id);
        } else {
            $accesses = $this->accessServices->getAccessesByCongressIdPginantion($congressId, $offset, $perPage, $search, $date, $startTime, $endTime, $isOnline, $myAccesses, null);
        }
        return response()->json($accesses, 200);
    }

}
