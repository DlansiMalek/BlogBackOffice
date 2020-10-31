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
class TrackingServices
{
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => UrlUtils::getElasticBaseUrl(),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'http_errors' => false
        ]);
    }

    public function sendUserInfo($congress, $user)
    {
        $formInputs = $congress->form_inputs;
        $congressId = $congress->congress_id;

        $object = $this->getBasicUserInfos($congressId, $user);

        $object = array_merge($object, Utils::mappingInputResponse($formInputs, $user->responses));

        $object['env'] = env('APP_ENV');

        $res = $this->client->post('/eventizer-tracking-users/_doc', [
            'body' => json_encode($object, true)
        ]);

        return json_decode($res->getBody(), true);
    }

    private function getBasicUserInfos($congressId, $user)
    {
        return array(
            "user_id" => strval($user->user_id),
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "mobile" => strval($user->mobile),
            "congress_id" => strval($congressId)
        );
    }
}
