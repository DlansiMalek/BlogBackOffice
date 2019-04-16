<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:42
 */

namespace App\Services;


use App\Models\Access_Vote;
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
        return Access_Vote::where('congress_id', '=', $congress_id)->get();
    }

    public function resetAssociation($congress_id)
    {
        return Access_Vote::where('congress_id', '=', $congress_id)->delete();
    }

}