<?php

namespace App\Services;

use App\Models\Action;
use App\Models\AttestationType;
use App\Models\CongressType;
use App\Models\Country;
use App\Models\Privilege;
use App\Models\PrivilegeConfig;
use App\Models\Service;
use App\Models\Etablissement;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\FormInputResponse;
use Illuminate\Support\Facades\Cache;


class SharedServices
{

  public function getAllPrivileges()
  {
    return Privilege::where('privilege_id', '>=', 3)->get();
  }

  public function getPrivilegeById($privilegeId)
  {
    return Privilege::where('privilege_id', '=', $privilegeId)
      ->first();
  }

  public function getPrivilegesWithBadges()
  {
    return Privilege::with(['badges'])
      ->get();
  }

  public function getAllServices()
  {
    return Service::all();
  }

  public function getAllEtablissements()
  {
    return Etablissement::all();
  }

  public function saveAttestationsSubmissionsInPublic(array $request)
  {
    if ($request) {
      try {
        $zipName = 'attestationsSubmission.zip';
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
          'POST',
          //          'http://127.0.0.1:8000'  
          UrlUtils::getUrlBadge() . '/badge/generateParticipantsPro/multiple',
          [
            'json' =>
            [
              'participants' => $request,
            ]

          ]
        );
        Storage::put($zipName, $res->getBody(), 'public');
        return $zipName;
      } catch (ClientException $e) {
        return null;
      }
    }
    return null;
  }

  public function saveAttestationSubmissionInPublic(array $request, $IdGenerator)
  {
    if ($request) {
      try {
        // 'http://127.0.0.1:8000'
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
          'POST',
          UrlUtils::getUrlBadge() . '/badge/generateParticipantPro',
          [
            'json' => [
              'badgeIdGenerator' => $IdGenerator,
              'fill' => $request
            ]
          ]
        );
        Storage::put('attestationSubmission.png', $res->getBody(), 'public');
        return true;
      } catch (ClientException $e) {
        return false;
      }
    }
  }

  public function saveBadgeInPublic($badge, $user, $qrCode, $privilegeId, $congress_id)
  {
    try {
      $client = new \GuzzleHttp\Client();
      $fill = $this->textMapping($badge, $user, $qrCode, $congress_id);
      $badgeIdGenerator = $badge['badge_id_generator'];
      $res = $client->request(
        'POST',
        UrlUtils::getUrlBadge() . '/badge/generateParticipantPro',
        [
          'json' => [
            'badgeIdGenerator' => $badgeIdGenerator,
            'fill' => $fill
          ]
        ]
      );
      Storage::put('badge.png', $res->getBody(), 'public');
      return true;
    } catch (ClientException $e) {
      return false;
    }
  }

  public function getAllTypesAttestation()
  {
    return AttestationType::with(['attestations'])
      ->get();
  }

  public function getAllCountries()
  {
  
  $cacheKey = config('cachedKeys.Countries') ;

  if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
  }

  $Countries= Country::all();
  Cache::put($cacheKey, $Countries, env(86400)); // 24 hours;

  return  $Countries;
  }

  public function getAllCongressTypes()
  {
    return CongressType::all();
  }

  public function textMapping($badge, $user, $qrCode, $congress_id)
  {
    $user_responses = $this->getQuestionResponsesForBadge($congress_id, $user->user_id);
    if ($user->name) {
      $mappingList = [
        'first_name' => $user->name,
        'last_name' => '',
        'email' => $user->email,
        'country' => '',
        'mobile' => $user->mobile
      ];
    } else {
      $mappingList = [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'country' => $user->country ? $user->country->name : '',
        'mobile' => $user->mobile
      ];
    }
    if ($user_responses != null) {
      $finalList = array_merge($user_responses, $mappingList);
    } else {
      $finalList = $mappingList;
    }
    $badgeParams = $badge['badge_param'];

    $params = [];
    foreach ($badgeParams as $param) {
      if ($param['key'] === 'default') {
        $params[] =
          ["key" => $param['key'], "value" => $finalList['first_name'] . ' ' . $finalList['last_name']];
      } else {
        $params[] =
          ["key" => $param['key'], "value" => $this->mappingBadgeKey($param['key'], $finalList)];
      }
    }
    return ['qrCode' => $qrCode, 'texts' => $params];
  }


  public function mappingBadgeKey($key, $mappingList)
  {
    $listParams = explode(',', $key);
    $val = '';
    foreach ($listParams as $k) {
      $val = $val . $mappingList[$k] . ' ';
    }
    return $val;
  }

  public function getActionByKey($key)
  {
    return Action::where('key', '=', $key)
      ->first();
  }

  public function getAllActions()
  {
    return Action::all();
  }

  public function submissionMapping($submission_title, $co_authors, $paramsSubmission)
  {
    $co_authors = json_decode($co_authors, true);
    $principal_author = array_shift($co_authors);
    $authors = "";
    for ($i = 0; $i < sizeof($co_authors); $i++) {
      $firstName = isset($co_authors[$i]['first_name'][0]) ? $co_authors[$i]['first_name'][0] : '';
      $authors .= strtoupper($firstName . '. ' . $co_authors[$i]['last_name']) . ' ,';
    }
    $authors = substr($authors, 0, -1);
    $mappingList = [
      'principal_author' => Utils::getFullName($principal_author['first_name'], $principal_author['last_name']),
      'submission_title' => $submission_title,
      'co-authors' => $authors
    ];
    $params = [];
    foreach ($paramsSubmission as $param) {

      $params[] =
        ["key" => $param['key'], "value" => $this->mappingBadgeKey($param['key'], $mappingList)];
    }
    return ['qrCode' => false, 'texts' => $params];
  }

  public function getQuestionResponsesForBadge($congress_id, $user_id)
  {
    $responses = FormInputResponse::whereHas('form_input',  function ($query) use ($congress_id) {
      $query->where('congress_id', '=', $congress_id);
    })->where('user_id', '=', $user_id)
      ->with(['form_input:form_input_id,key', 'values'])
      ->get();
    $user_responses = [];
    foreach ($responses as $param) {
      if ($param['response'] == null) {
        $user_responses[$param['form_input']['key']] = "";
      } else if ($param['response'] != "") {
        $user_responses[$param['form_input']['key']] = $param['response'];
      } else {
        $user_responses[$param['form_input']['key']] = $param['values'][0]['val']['value'];
      }
    }
    return $user_responses;
  }
}
