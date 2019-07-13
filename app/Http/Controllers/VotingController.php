<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:36
 */

namespace App\Http\Controllers;

use App\Models\Congress;
use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\UserServices;
use App\Services\VotingServices;
use Illuminate\Http\Request;


class VotingController extends Controller
{

    protected $votingService;
    protected $adminService;
    protected $congressServices;
    protected $userService;

    function __construct(VotingServices $votingService, AdminServices $adminServices, UserServices $userServices, CongressServices $congressServices)
    {
        $this->votingService = $votingService;
        $this->adminService = $adminServices;
        $this->userService = $userServices;
        $this->congressServices = $congressServices;
    }


    public function setToken(Request $request, $congress_id)
    {
        if (!$request->has('token')) return response()->json(['error' => 'no token in request'], 400);
        if (!$congressConfig = $this->congressServices->getCongressConfig($congress_id)) return response()->json(['error' => 'Congress not found'], 404);
        $congressConfig->voting_token = $request->get('token');
        $congressConfig->update();
        return $congressConfig->voting_token;
    }

    public function getToken(Request $request, $congress_id)
    {
        if (!$congress = $this->congressServices->getCongressConfig($congress_id)) return response()->json(['error' => 'Congress Not Found'], 404);
        return $congress->voting_token;
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
            else if ($old->access_id != $newAssociation['access_id'] || $old->vote_id != $newAssociation['vote_id'] || $old->congress_id != $congress_id) {
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

    public function resetAssociation($congress_id)
    {
        $this->votingService->resetAssociation($congress_id);
        return [];
    }

    public function getListPolls(Request $request)
    {
        $token = $request->query("token");

        $userResponse = $this->votingService->signinUser($token);

        return $this->votingService->getListPolls($userResponse['token']);
    }

    public function getMultipleListPolls(Request $request)
    {
        $res = [];
        foreach ($request->all() as $token) {
            $userResponse = $this->votingService->signinUser($token);

            $temp = $this->votingService->getListPolls($userResponse['token']);
            if ($temp && count($temp)) {
                $res = array_merge($res, $temp);
            }
        }
        return $res;
    }

    public function sendScores(Request $request)
    {
        $scoreVotes = $request->all();
        foreach ($scoreVotes as $scoreVote) {
            if ($this->userService->getUserById($scoreVote['userId']) && $this->votingService->getAccessVoteById($scoreVote['accessVoteId'])) {
                if (!$oldVoteScore = $this->votingService->getByUserIdAndAccessVote($scoreVote['userId'], $scoreVote['accessVoteId']))
                    $this->votingService->addScore($scoreVote);
                else
                    $this->votingService->updateScore($oldVoteScore, $scoreVote);
            }
        }
        return response()->json(["message" => "adding successs"], 200);
    }

    public function getQuiz(Request $request)
    {
        $tokens = [];
        $associations = [];
        foreach ($request->all() as $congress_id) {
            $token = $this->congressServices->getCongressConfig($congress_id)->voting_token;
            if ($token && !in_array($token, $tokens)) array_push($tokens, $token);
            $a = $this->votingService->getAssociations($congress_id);
            $associations = array_merge($associations, (array) $a);
        }
        $polls = [];
        foreach ($tokens as $token) {
            $userResponse = $this->votingService->signinUser($token);
            $p = $this->votingService->getListPolls($userResponse['token']);
            $polls = array_merge($polls, $p);
        }

        return response()->json(['quiz' => $polls, 'associations' => $associations], 200);
    }
}
