<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:42
 */

namespace App\Services;


use App\Models\Access;
use App\Models\Access_Vote;
use App\Models\Admin;
use App\Models\Vote_Score;
use GuzzleHttp\Client;

/**
 * @property \GuzzleHttp\Client client
 */
class VotingServices
{
    public function __construct()
    {
        $this->client = new Client([
            // 'base_uri' => 'http://localhost:3000', // Testing Local VayeCongress Local VayeVoting
             'base_uri' => 'http://137.74.165.25:3001/', // Testing Local VayeCongress Server VayeVoting
//            'base_uri' => 'http://appvoting-server:3000',
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
        $accessVote = new Access_Vote();
        $accessVote->access_id = $newAssociation['access_id'];
        $accessVote->vote_id = $newAssociation['vote_id'];
        $accessVote->congress_id = $congress_id;
        $accessVote->save();
    }

    public function getAssociations($congress_id)
    {
        return $res = Access_Vote::where('congress_id', '=', $congress_id)->get();
    }

    public function resetAssociation($congress_id)
    {
        return Access_Vote::where('congress_id', '=', $congress_id)->delete();
    }

    public function addScore($scoreVoteData)
    {
        $scoreVote = new Vote_Score();
        $scoreVote->user_id = $scoreVoteData['userId'];
        $scoreVote->access_vote_id = $scoreVoteData['accessVoteId'];
        $scoreVote->score = $scoreVoteData['score'];
        $scoreVote->save();
    }

    public function getByUserIdAndAccessVote($userId, $accessVoteId)
    {
        return Vote_Score::where('user_id', '=', $userId)
            ->where('access_vote_id', '=', $accessVoteId)
            ->first();
    }

    public function updateScore($oldVoteScore, $scoreVoteData)
    {
        $oldVoteScore->score = $scoreVoteData['score'];
        $oldVoteScore->update();
    }

    public function getAccessVoteById($accessVoteId)
    {
        return Access_Vote::where('access_vote_id', '=', $accessVoteId)
            ->first();
    }

}