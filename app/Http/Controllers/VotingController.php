<?php

namespace App\Http\Controllers;


use App\Services\AdminServices;
use App\Services\VotingService;
use Illuminate\Http\Request;


class VotingController extends Controller
{

    protected $votingService;
    protected $adminService;

    function __construct(VotingService $votingService, AdminServices $adminServices)
    {
        $this->votingService = $votingService;
        $this->adminService = $adminServices;
    }


    public function setToken(Request $request)
    {
        if (!$request->has('token')) return response()->json(['error' => 'no token in request'], 400);
        if (!$admin = $this->adminService->getConnectedAdmin($request)) return response()->json(['error' => 'Unauthorized'], 403);
        $admin->voting_token = $request->get('token');
        $admin->update();
        $personnel = $this->adminService->getListPersonelsByAdmin($admin->admin_id);
        if ($personnel)
            foreach ($personnel as $p) {
                $p->voting_token = $request->get('token');
                $p->update();
            }
        return $admin->voting_token;
    }

    public function getToken(Request $request)
    {
        if (!$admin = $this->adminService->getConnectedAdmin($request)) return response()->json(['error' => 'Unauthorized'], 403);
        return $admin->voting_token;
    }

    public function setAssociation(Request $request, $congress_id)
    {
        if (!$oldAssociation = $this->votingService->getAssociations($congress_id)) $oldAssociation = [];
        foreach ($oldAssociation as $old) {
            $found = false;
            $newAssociation = null;
            foreach ($request->all() as $q) {
                if (array_key_exists('access_vote_id', $q) && $q['access_vote_id'] == $old->access_vote_id) {
                    $found = true;
                    $newAssociation = $q;
                    break;
                }
            }
            if (!$found) $old->delete();
            else if ($old->access_id != $newAssociation['access_id'] || $old->vote_id != $newAssociation['vote_id'] || $old->congress_id !=$congress_id) {
                $old->access_id = $newAssociation['access_id'];
                $old->vote_id = $newAssociation['vote_id'];
                $old->congress_id = $congress_id;
                $old->update();
            }
        }

        foreach ($request->all() as $newAssociation) {
            $found = false;
            foreach ($oldAssociation as $old) {
                if (array_key_exists('access_vote_id', $newAssociation) && $old->access_vote_id == $newAssociation['access_vote_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) $this->votingService->saveAssociation($newAssociation, $congress_id);
        }
        return $this->votingService->getAssociations($congress_id);

    }

    public function getAssociation($congress_id)
    {
        return $this->votingService->getAssociations($congress_id);
    }

    public function resetAssociation($congress_id){
        $this->votingService->resetAssociation($congress_id);
        return [];
    }
}
