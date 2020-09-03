<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:42
 */

namespace App\Services;

use App\Models\AccessVote;
use App\Models\VoteScore;
use GuzzleHttp\Client;

/**
 * @property \GuzzleHttp\Client client
 */
class VotingServices
{
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => UrlUtils::getVayeVotingUrl(),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);

    }

    public function signinUser($uuid)
    {
        $res = $this->client->post('/api/users/auth/signin', [
            'form_params' => [
                "uuid" => $uuid
            ]
        ]);

        return json_decode($res->getBody(), true);
    }

    public function getListPolls($token)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];


        $res = $this->client->get('/api/polls', [
            'headers' => $headers
        ]);

        return json_decode($res->getBody(), true);
    }

    public function saveAssociation($newAssociation, $congress_id)
    {
        $accessVote = new AccessVote();
        $accessVote->access_id = $newAssociation['access_id'];
        $accessVote->vote_id = $newAssociation['vote_id'];
        $accessVote->congress_id = $congress_id;
        $accessVote->save();
    }

    public function getAssociations($congress_id)
    {
        return $res = AccessVote::with(['scores.user', 'access'])->where('congress_id', '=', $congress_id)->get();
    }

    public function resetAssociation($congress_id)
    {
        return AccessVote::where('congress_id', '=', $congress_id)->delete();
    }


    public function getAccessVoteById($accessVoteId)
    {
        return AccessVote::where('access_vote_id', '=', $accessVoteId)
            ->first();
    }

    public function addScore($scoreVoteData)
    {
        $scoreVote = new VoteScore();
        $scoreVote->user_id = $scoreVoteData['userId'];
        $scoreVote->access_vote_id = $scoreVoteData['accessVoteId'];
        $scoreVote->score = $scoreVoteData['score'];
        $scoreVote->num_user_vote = $scoreVoteData['userNumber'];
        $scoreVote->save();
    }

    public function getByUserIdAndAccessVote($userId, $accessVoteId)
    {
        return VoteScore::where('user_id', '=', $userId)
            ->where('access_vote_id', '=', $accessVoteId)
            ->first();
    }

    public function updateScore($oldVoteScore, $scoreVoteData)
    {
        $oldVoteScore->score = $scoreVoteData['score'];
        $oldVoteScore->update();
    }


}
