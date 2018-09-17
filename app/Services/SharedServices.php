<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Lieu_Ex;
use Illuminate\Support\Facades\Log;
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

    public function saveFileInPublic($badgeIdGenerator, $name, $qrCode)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST',
            Utils::$baseUrlBadge .'/badge/generateParticipant', [
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