<?php

namespace App\Services;

use App\Models\Attestation_Type;
use App\Models\Country;
use App\Models\Feedback_Question_Type;
use App\Models\Form_Input_Type;
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
            Utils::$baseUrlBadge . '/badge/generateParticipant', [
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
        return Attestation_Type::with(['attestations'])
            ->get();
    }

    public function getAllCountries()
    {
        return Country::all();
    }

    public function getFormInputTypes()
    {
        return Form_Input_Type::get();
    }


}