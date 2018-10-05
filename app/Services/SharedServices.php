<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Lieu_Ex;
use App\Models\Privilege;
use Illuminate\Support\Facades\Storage;


class SharedServices
{

    public function getAllGrades()
    {
        return Grade::all();
    }

    public function getAllLieux()
    {
        return Lieu_Ex::all();
    }

    public function getAllPrivileges()
    {
        return Privilege::where('privilege_id', '>=', 3)->get();
    }

    public function getPrivilegesWithBadges()
    {
        return Privilege::with(['badges'])
            ->get();
    }

    public function saveFileInPublic($badgeIdGenerator, $name, $qrCode)
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
}