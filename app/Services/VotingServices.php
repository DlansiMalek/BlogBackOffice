<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:42
 */

namespace App\Services;


use GuzzleHttp\Client;

/**
 * @property \GuzzleHttp\Client client
 */
class VotingServices
{

    public function __construct()
    {
        $this->client = new Client([
            // 'base_uri' => 'http://localhost:3000',
            'base_uri' => 'http://appvoting-server',
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

}