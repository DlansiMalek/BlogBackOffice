<?php

namespace App\Services;

use App\Models\AttestationType;
use App\Models\CongressType;
use App\Models\Country;
use App\Models\Privilege;
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

    public function saveBadgeInPublic($badgeIdGenerator, $name, $qrCode)
    {
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
        return 'badge.png';
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