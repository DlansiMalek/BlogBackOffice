<?php

namespace App\Services;

use App\Models\AttestationType;
use App\Models\CongressType;
use App\Models\Country;
use App\Models\Privilege;
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

    public function saveBadgeInPublic($badgeIdGenerator, $name, $qrCode)
    {
        return false;
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST',
                UrlUtils::getUrlBadge() . '/badge/generateParticipant', [
                    'json' => [
                        'badgeIdGenerator' => $badgeIdGenerator,
                        'participant' => [
                            'name' => $name,
                            'qrCode' => $qrCode
                        ]
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


}
