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
        if (env('ELASTIC_DISABLED')) {
            return true;
        }

        $object = $this->getBasicUserInfos($congressId, $user);

        $object = array_merge($object, Utils::mappingInputResponse($formInputs, $user->responses));

        if (sizeof($user->user_congresses) > 0) {
            $object['privilege_name'] = $user->user_congresses[0]->privilege->name;
            $user->user_congresses[0]->is_tracked = 1;
            $user->user_congresses[0]->update();
        }

        $env = env('APP_ENV');
        $object['env'] = $env;

        $res = $this->client->post('/eventizer-tracking-users-' . $env . '-' . $congressId . '/_doc', [
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

    public function groupByActionIds($traking, $actionIds, $type = null)
    {
        $traking = json_decode($traking, true);
        return array_values(array_filter($traking, function ($item) use ($actionIds, $type) {
            $index = array_search($item['action_id'], $actionIds);
            return $index >= 0 && (!$type || $type === $item['type']);
        }));
    }

    public function sendTrackingPair($actionName, $trackings, $actionEntryId, $actionLeaveId)
    {
        $index = 0;
        while (sizeof($trackings) > $index) {
            $dateEntry = null;
            $dateLeave = null;
            while (sizeof($trackings) > $index && $trackings[$index]['action_id'] != $actionEntryId) {
                $index++;
            }
            if (sizeof($trackings) > $index) {
                $dateEntry = $trackings[$index]['date'];
            }
            while (sizeof($trackings) > $index && $trackings[$index]['action_id'] != $actionLeaveId) {
                $index++;
            }
            if (sizeof($trackings) > $index) {
                $dateLeave = $trackings[$index]['date'];
            }

            if ($dateEntry && $dateLeave) {
                $this->sendTracking($trackings[$index], $actionName, $dateEntry, $dateLeave);
            }
        }
    }

    private function sendTracking($tracking, $actionName = null, $dateEntry = null, $dateLeave = null)
    {
        if (env('ELASTIC_DISABLED')) {
            return true;
        }

        $form_params = array(
            'user_id' => strval($tracking['user_id']),
            'comment' => $tracking['comment'] ? $tracking['comment'] : "",
            'user_call_id' => isset($tracking['user_call_id']) ? strval($tracking['user_call_id']) : "",
            'env' => env('APP_ENV'),
            'congress_id' => strval($tracking['congress_id'])
        );

        if ($actionName) {
            $obj = array(
                'action' => $actionName,
                'date_entry' => $dateEntry,
                'date_leave' => $dateLeave,
                'duration' => strval((Utils::diffMinutes($dateEntry, $dateLeave) * 60000))
            );
        } else {
            $obj = array(
                'action' => $tracking['action']['key'],
                'date' => $tracking['date']
            );
        }

        if ($tracking['type'] == 'ACCESS') {
            $obj['channel_name'] = $tracking['access']['name'];
            $obj['type'] = $tracking['type'];
        }

        if ($tracking['type'] == 'STAND') {
            $obj['channel_name'] = $tracking['stand']['name'];
            $obj['type'] = $tracking['type'];
        }

        $form_params = array_merge($form_params, $obj);

        $res = $this->client->post('/eventizer-tracking-tracks/_doc', [
            'body' => json_encode($form_params, true)
        ]);

        return json_decode($res->getBody(), true);
    }

    public function sendTrackingNormal($trackings)
    {
        if (env('ELASTIC_DISABLED')) {
            return true;
        }

        foreach ($trackings as $tracking) {
            $this->sendTracking($tracking);
        }
    }

    public function getTrackings($congress_id, $request)
    {
        $env = env('APP_ENV');
        $res = $this->client->post('/eventizer-tracking-tracks-' . $env . '-' . $congress_id . '/_search', [
            'body' => json_encode($request->all())
        ]);

        return json_decode($res->getBody(), true);
    }


    public function createIndexByCongress($congress_id)
    {
        $env = env('APP_ENV');

        $data = array(
            'mappings' => array (
                'properties' => array (
                    'user_id' => array(
                        'type' => 'text',
                        'fields' => array (
                            'keyword' => array (
                                'type' => 'keyword',
                                'ignore_above' => 256
                            )
                        )
                    )
                )
            )
        );

        $res = $this->client->put('/eventizer-tracking-users-' . $env . '-' . $congress_id, [
            'body' => json_encode($data, true)
        ]);

        return json_decode($res->getBody(), true);
    }

    public function enrichPolicyByCongress($congress_id)
    {
        $env = env('APP_ENV');

        $data = array(
            'match' => array(
                'indices' => 'eventizer-tracking-users-' . $env . '-' . $congress_id,
                'match_field' => 'user_id',
                'enrich_fields' => [
                    '*'
                ],
                'query' => array(
                    'bool' => array(
                        'must' => array(
                            array(
                                'match' => array(
                                    'congress_id' => $congress_id
                                )
                            ),
                            array(
                                'match' => array(
                                    'env' => $env
                                )
                            )
                        )
                    )
                )

            )
        );

        $res = $this->client->put('/_enrich/policy/eventizer-tracking-users-' . $env . '-' . $congress_id, [
            'body' => json_encode($data, true)
        ]);

        return json_decode($res->getBody(), true);
    }

    public function executePolicy($congress_id)
    {
        $env = env('APP_ENV');
        $res = $this->client->post('/_enrich/policy/eventizer-tracking-users-' . $env . '-' . $congress_id .'/_execute');
        return json_decode($res->getBody(), true);
    }

    public function enrichPolicyByUserDetails($congress_id)
    {
        $env = env('APP_ENV');
        $data = array(
            'processors' => array(
                array(
                    'enrich' => array(
                        'policy_name' => 'eventizer-tracking-users-' . $env . '-' . $congress_id,
                        'field' => 'user_id',
                        'target_field' => 'user',
                        'max_matches' => '1'
                    )
                ),
                array(
                    'script' => array(
                        'lang' => 'painless',
                        'source' =>  "\n      if (!ctx.containsKey('date')) {\n        ctx.date = ctx.date_entry;\n      }\n    ",
                    )
                )
            )
        );
        $res = $this->client->put('/_ingest/pipeline/eventizer-tracking-user-lookup-' . $env . '-' . $congress_id, [
            'body' => json_encode($data, true)
        ]);

        return json_decode($res->getBody(), true);
    }
}
