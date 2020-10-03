<?php

namespace App\Http\Controllers;


use App\Services\CongressServices;
use App\Services\StandServices;
use Illuminate\Http\Request;


class StandController extends Controller
{

    protected $standServices;
    protected $congressServices;

    function __construct(StandServices $standServices, CongressServices $congressServices)
    {
        $this->standServices = $standServices;
        $this->congressServices = $congressServices;
    }


    public function getStands($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $stands = $this->standServices->getStands($congress_id);
        return response()->json($stands, 200);
    }

    public function editStands($congress_id, $stand_id, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        if (!$stand = $this->congressServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        if (!$request->has('url_streaming')) {
            return response()->json(['response' => 'bad request'], 400);
        }
        $url_streaming = $request->input('url_streaming');
        $stand = $this->congressServices->editStands($congress_id, $stand_id, $url_streaming);
        return response()->json($stand, 200);
    }

    public function getDocsByCongress($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $name = $request->query('name', '');

        $stands = $this->congressServices->getStands($congressId, $name);

        $docs = $this->congressServices->getDocsByStands($stands);

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
}
