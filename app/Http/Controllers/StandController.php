<?php

namespace App\Http\Controllers;


use App\Services\CongressServices;
use App\Services\StandServices;
use App\Services\VotingServices;
use App\Services\AccessServices;
use Illuminate\Http\Request;

class StandController extends Controller
{

    protected $standServices;
    protected $congressServices;
    protected $votingServices;

    function __construct(StandServices $standServices, 
                        CongressServices $congressServices,
                        VotingServices $votingServices,
                        AccessServices $accessServices)
    {
        $this->standServices = $standServices;
        $this->congressServices = $congressServices;
        $this->votingServices = $votingServices;
        $this->accessServices = $accessServices;
    }


    public function getStands($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $stands = $this->standServices->getStands($congress_id);
        return response()->json($stands, 200);
    }
    function addStand (Request $request) {
     
        $stand = $this->standServices->addStand(
            $request->input('name'),
            $request->input('organization_id'),
            $request->input('congress_id')
 
         );
         $resources = $request->input('docs');
         $this->standServices->saveResourceStand($resources,$stand->stand_id);
         return response()->json('Stand added',200);
     }
 
 
     public function getStandById ($congressId,$stand_id)
     {   return $this->standServices->getStandById($stand_id);
         
     }
 
     function editStand (Request $request, $congress_id, $stand_id) {
        if (! $oldStand = $this->standServices->getStandById($stand_id)) {
            return response()->json('stand not found',404);
        }
      
    
  
       return response()->json('stand updated',200);
      }
 
      public function deleteStand($congress_id , $stand_id)
      {  
          if (!$stand = $this->standServices->getStandById($stand_id)) {
          return response()->json('no stand found' ,404);
      }
        $stand->delete();
         return response()->json(['response' => 'stand deleted'],200);
      }

    public function editStands($congress_id, $stand_id, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        if (!$oldStand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        $url_streaming = $request->has('url_streaming') ? $request->input('url_streaming') : null;
        $stand = $this->standServices->editStand(
            $oldStand,
            $request->input('name'),
            $request->input('congress_id'),
            $request->input('organization_id'),
            $request->input('url_streaming')
         );
          $this->standServices->saveResourceStand($request->input('docs'),$stand->stand_id);
        return response()->json($stand, 200);
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

        if ($all) {
            $this->standServices->modifyAllStatusStand($congressId, $status);
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
}
