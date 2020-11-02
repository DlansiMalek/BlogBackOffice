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

    public function sendUserInfo($congressId, $formInputs, $user)
    {

        $object = $this->getBasicUserInfos($congressId, $user);

        $object = array_merge($object, Utils::mappingInputResponse($formInputs, $user->responses));

        if (sizeof($user->user_congresses) > 0) {
            $object['privilege_name'] = $user->user_congresses[0]->privilege->name;
            $user->user_congresses[0]->is_tracked = 1;
            $user->user_congresses[0]->update();
        }

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
