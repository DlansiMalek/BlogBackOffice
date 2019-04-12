<?php

namespace App\Services;

use App\Models\Access_Vote;
use JWTAuth;
use PDF;


/**
 * @property VotingService $votingService
 */
class VotingService
{


    public function saveAssociation($newAssociation, $congress_id)
    {
        $accessVote = new Access_Vote();
        $accessVote->access_id = $newAssociation['access_id'];
        $accessVote->vote_id = $newAssociation['vote_id'];
        $accessVote->congress_id = $congress_id;
        $accessVote->save();
    }

    public function getAssociations($congress_id)
    {
        return Access_Vote::where('congress_id', '=', $congress_id)->get();
    }

    public function resetAssociation($congress_id)
    {
        return Access_Vote::where('congress_id', '=', $congress_id)->delete();
    }
}
