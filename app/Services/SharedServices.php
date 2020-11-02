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
use Illuminate\Support\Facades\Storage;


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


    public function getAllPrivilegesCorrespondence($congress_id)

    {
        $privilegeBase = Privilege::where('priv_reference', '=', null)
            ->with(['privilegeConfig' => function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            }])
            ->get()->toArray();
        for ($i = 0; $i < count($privilegeBase); $i++) {
            if ($privilegeBase[$i]['privilege_config'] != null) {
                unset($privilegeBase[$i]);
            }
        }
        $newPrivileges = Privilege::join('Privilege_Config', function ($join) use ($congress_id) {
            $join->on('Privilege.privilege_id', '=', 'Privilege_Config.privilege_id')
                ->where('Privilege_Config.congress_id', '=', $congress_id)
                ->where('Privilege_Config.status', '=', 1);
        })->get()->toArray();
        $result = array_merge($privilegeBase, $newPrivileges);
        return $result;
    }

    public function getPrivilegesDeBase()
    {
        $privilegeBase = Privilege::where('priv_reference', '=', null)
            ->get();
        return $privilegeBase;

    }

    public function getPrivilegesByCongress($congress_id)
    {
        $privilegeBase = Privilege::where('priv_reference', '=', null)
            ->with(['privilegeConfig' => function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            }])
            ->get()->toArray();
        $otherPrivileges = Privilege::where('priv_reference', '!=', null)
        ->whereHas('privilegeConfig', function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            })
            ->with(['privilegeConfig', 'privilege'])
            ->get()->toArray();
        $result = array_merge($privilegeBase, $otherPrivileges);
        return $result;

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
            $zipName = 'attestationsSubmission.zip';
            $client = new \GuzzleHttp\Client();

            $res = $client->request('POST',
//          'http://127.0.0.1:8000'  
                UrlUtils::getUrlBadge() . '/badge/generateParticipantsPro/multiple', [
                    'json' =>
                        [
                            'participants' => $request,
                        ]

                ]);
            Storage::put($zipName, $res->getBody(), 'public');
            return $zipName;
        }
    }

    public function saveAttestationSubmissionInPublic(array $request, $IdGenerator)
    {
        if ($request) {
            try {
                // 'http://127.0.0.1:8000'
                $client = new \GuzzleHttp\Client();
                $res = $client->request('POST',
                    UrlUtils::getUrlBadge() . '/badge/generateParticipantPro', [
                        'json' => [
                            'badgeIdGenerator' => $IdGenerator,
                            'fill' => $request
                        ]
                    ]);
                Storage::put('attestationSubmission.png', $res->getBody(), 'public');
                return true;
            } catch (ClientException $e) {
                return false;
            }
        }
    }

    public function saveBadgeInPublic($badge, $user, $qrCode, $privilegeId)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $fill = $this->textMapping($badge, $user, $qrCode);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $res = $client->request('POST',
                UrlUtils::getUrlBadge() . '/badge/generateParticipantPro', [
                    'json' => [
                        'badgeIdGenerator' => $badgeIdGenerator,
                        'fill' => $fill
                    ]
                ]);
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
        return Country::all();
    }

    public function getAllCongressTypes()
    {
        return CongressType::all();
    }

    public function textMapping($badge, $user, $qrCode)
    {
        if ($user->first_name) {
            $mappingList = ['first_name' => $user->first_name,
                'last_name' => '',
                'email' => $user->email,
                'country' => '',
                'mobile' => $user->mobile];
        } else {
            $mappingList = ['first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'country' => $user->country->name,
                'mobile' => $user->mobile];
        }
        $badgeParams = $badge['badge_param'];

        $params = [];
        foreach ($badgeParams as $param) {
            if ($param['key'] === 'default') {
                $params[] =
                    ["key" => $param['key'], "value" => $mappingList['first_name'] . ' ' . $mappingList['last_name']];

            } else {
                $params[] =
                    ["key" => $param['key'], "value" => $this->mappingBadgeKey($param['key'], $mappingList)];
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

    public function submissionMapping($submission_title, $principal_author, $co_authors, $paramsSubmission)
    {
        $authors = "";
        foreach ($co_authors as $author) {
            $authors .= strtoupper($author->first_name[0] . '. ' . $author->last_name) . ' ,';
        }
        $authors = substr($authors, 0, -1);
        $mappingList = ['principal_author' => $principal_author,
            'submission_title' => $submission_title,
            'co-authors' => $authors,];
        $params = [];
        foreach ($paramsSubmission as $param) {

            $params[] =
                ["key" => $param['key'], "value" => $this->mappingBadgeKey($param['key'], $mappingList)];

        }
        return ['qrCode' => false, 'texts' => $params];
    }


}
