<?php

namespace App\Http\Controllers;


use App\Services\CongressServices;
use App\Services\StandServices;
use App\Services\VotingServices;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\STagServices;
use Illuminate\Http\Request;

class StandController extends Controller
{

    protected $standServices;
    protected $congressServices;
    protected $votingServices;
    protected $adminServices;
    protected $stagServices;

    function __construct(
        StandServices $standServices,
        CongressServices $congressServices,
        VotingServices $votingServices,
        AccessServices $accessServices,
        AdminServices $adminServices,
        STagServices $stagServices
    ) {
        $this->standServices = $standServices;
        $this->congressServices = $congressServices;
        $this->votingServices = $votingServices;
        $this->accessServices = $accessServices;
        $this->adminServices = $adminServices;
        $this->stagServices = $stagServices;
    }

    
    public function getStands($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $stands = $this->standServices->getStands($congress_id);
        return response()->json($stands, 200);
    }
    function addStand($congressId, Request $request)
    {
        if (!$request->has(['name', 'organization_id'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['name', 'organization_id']], 404);
        }

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        $stand = null;
        if ($request->has('stand_id')) {
            $stand = $this->standServices->getStandById($request->input('stand_id'));
        }
        $stand = $this->standServices->addStand($stand, $congressId, $request);
        $this->stagServices->deleteOldSTags($stand->stand_id);
        $this->stagServices->addAllStandTags($request->input('tag_id_selected'), $stand->stand_id);
        $this->standServices->saveResourceStand($request->input('docs'), $stand->stand_id); 
        return response()->json($stand, 200);
    }

    public function getStandById($congressId, $stand_id)
    {
        return $this->standServices->getStandById($stand_id);
    }

    public function deleteStand($congress_id, $stand_id)
    {
        if (!$stand = $this->standServices->getStandById($stand_id)) {
            return response()->json('no stand found', 404);
        }
        $stand->delete();
        return response()->json(['response' => 'stand deleted'], 200);
    }

    public function getDocsByCongress($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $name = $request->query('name', '');

        $stands = $this->standServices->getStands($congressId, $name);

        $docs = $this->standServices->getDocsByStands($stands);

        return response()->json($docs);
    }

    public function getAllUrlsByCongressId($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $name = $request->query('name', '');

        $stands = $this->standServices->getStands($congressId, $name);

        $accesses = $this->congressServices->getAccesssByCongressId($congressId, $name);

        $accesses = $this->votingServices->getQuizInfosByAccesses($congress->config->voting_token, $accesses);

        $urls = $this->standServices->getUrlsByStandsAndAccess($stands, $accesses);

        return response()->json($urls);
    }

    public function modiyStatusStand($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $all = $request->query('all', false);
        $status = $request->query('status', 1);
        $stand_id = $request->query('standId', null);

        if ($all == 'true') {
            $this->standServices->modifyAllStatusStand($congressId, $status);
        } else {
            $this->standServices->modifyStatusStand($stand_id, $status);
        }

        return response()->json($this->standServices->getStands($congressId));
    }

    public function getStatusStand($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $global = $request->query('global', false);

        $stands = $this->standServices->getStands($congressId);
        if ($global) {
            return response()->json(['status' => $this->standServices->getStatusGlobalStand($stands)]);
        }

        return response()->json($stands);
    }

    public function getAllAccessStandByCongressId($congress_id)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $stands = $this->standServices->getAllStandByCongressId($congress_id);
        $accesses = $this->accessServices->getAccesssByCongressId($congress_id);
        return response()->json(['stands' => $stands, 'accesses' => $accesses]);
    }

    public function getStandsByCongress($congress_id, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $stag_id = $request->query('stag_id','');
        $stands = $this->standServices->getCachedStands($congress_id,$page,$perPage,$stag_id);
        return response()->json($stands, 200);
    }

    public function get3DBooths($congressId, Request $request) {

        $perPage = $request->query('perPage', 10);
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $stands = $this->standServices->getStandsPagination($congressId, $perPage);

        return response()->json($stands, 200);
    }

    public function getAllStandTypes()
    {
        $types = $this->standServices->getAllStandTypes();
        return response()->json($types, 200);
    }
    
    public function getContentConfigByStandType($stand_id, $stand_type_id)
    {
        if (!$this->standServices->getStandTypeById($stand_type_id))
            return response()->json('No type found', 404);
        $config = $this->standServices->getContentConfigByStandType($stand_id, $stand_type_id);
        return response()->json($config, 200);
    }

    public function editStandContentFiles($stand_id, $stand_type_id, Request $request)
    {
        if (!$this->adminServices->retrieveAdminFromToken())
            return response()->json(['error' => 'admin not found'], 404);

        if (!$stand = $this->standServices->getStandById($stand_id))
            return response()->json(['error' => 'stand not found'], 404);

        if (!$this->standServices->getStandTypeById($stand_type_id))
            return response()->json('No type found', 404);
        $data = $request->all();
        $this->standServices->editStandType($stand_type_id, $stand);
        $this->standServices->editStandContentFiles($data, $stand_id);
        return response()->json(['response' => 'added successfully'], 200);
    }

    public function deleteStandContentFiles($stand_content_file_id)
    {
        $contentFile = $this->standServices->getStandContentFile($stand_content_file_id);
        $contentFile->delete();
        return response()->json(['response' => 'deleted successfully'], 200);
    }
}
