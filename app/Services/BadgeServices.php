<?php

namespace App\Services;


use App\Models\AccessPresence;
use App\Models\Attestation;
use App\Models\AttestationAccess;
use App\Models\AttestationDivers;
use App\Models\Badge;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class BadgeServices
{


    public function uploadBadge($badge, $path, $congressId)
    {
        if (!$badge) {
            $badge = new Badge();
        }
        $badge->img_name = $path;
        $badge->qr_code_choice = -1;
        $badge->text_choice = -1;
        $badge->congress_id = $congressId;
        $badge->save();


        return $badge;
    }

    public function getBadgeByCongress($congressId)
    {
        return Badge::where("congress_id", "=", $congressId)
            ->first();
    }

    public function validerAttestationType($congressId, $attestationIdGenerator, $type)
    {
        $attestation = new AttestationDivers();
        $attestation->attestation_generator_id = $attestationIdGenerator;
        $attestation->attestation_type_id = $type;
        $attestation->congress_id = $congressId;
        $attestation->save();
    }

    public function validerBadge($congressId, $badgeIdGenerator, $privilegeId)
    {
        $badge = new Badge();
        $badge->congress_id = $congressId;
        $badge->badge_id_generator = $badgeIdGenerator;
        $badge->privilege_id = $privilegeId;
        $badge->save();

        return $badge;
    }

    public function impressionBadge()
    {

        $html = View::make('pdf.test')->render();
        $conv = new \Anam\PhantomMagick\Converter();
        return $conv
            ->addPage('<html><body><h1>Welcome to PhantomMagick</h1></body></html>')
            ->toPng()
            ->save(public_path() . '/google.png');
    }

    public function validerAttestation($congressId, $attesationIdGenerator, $blank)
    {
        $attestation = new Attestation();
        $attestation->congress_id = $congressId;
        if ($blank == 1) {
            $attestation->attestation_generator_id_blank = $attesationIdGenerator;
        } else {
            $attestation->attestation_generator_id = $attesationIdGenerator;
        }
        $attestation->save();
    }

    public function getAttestationByCongress($congressId)
    {
        return Attestation::where('congress_id', '=', $congressId)
            ->first();
    }

    public function getAttestationByCongressAndAccess($accessId)
    {
        return AttestationAccess::where('access_id', '=', $accessId)
            ->first();
    }

    public function validerAttestationAccess($accessId, $attesationIdGenerator)
    {
        $attestationAccess = new AttestationAccess();
        $attestationAccess->access_id = $accessId;
        $attestationAccess->attestation_generator_id = $attesationIdGenerator;
        $attestationAccess->save();
        return $attestationAccess;
    }

    public function getAttestationEnabled($user_id, $access)
    {
        $presenceAccess = AccessPresence::where('user_id', '=', $user_id)
            ->where('access_id', '=', $access->access_id)
            ->orderBy('entered_at', 'asc')
            ->get();
        if (sizeof($presenceAccess) == 0) {
            return 0;
        }

        $startCongress = $access->start_date;
        $endCongress = date('Y-m-d H:i:s', strtotime($startCongress . ' + ' . $access->duration . ' minute'));

        $calculatedTime = 0;
        foreach ($presenceAccess as $item) {
            if ($item->left_at == null) {
                $diff = Utils::diffMinutes($item->entered_at, $endCongress);
            } else {
                $diff = Utils::diffMinutes($item->entered_at, $item->left_at);
            }
            $calculatedTime += $diff;
        }
        if ($calculatedTime >= $access->seuil) {
            return array('enabled' => 1, 'time' => $calculatedTime);
        } else {
            return array('enabled' => 2, 'time' => $calculatedTime);
        }
    }

    public function getBadgeByCongressAndPrivilege($congressId, $privilegeId)
    {
        return Badge::where('congress_id', '=', $congressId)
            ->where('privilege_id', '=', $privilegeId)
            ->first();
    }


    public function saveAttestationsInPublic(array $request)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST',
            UrlUtils::getUrlBadge() . '/badge/generateParticipantsAll', [
                'json' => [
                    'data' => $request
                ]
            ]);
        Storage::put('attestations.zip', $res->getBody(), 'public');
        return 'attestations.zip';
    }

    public function getAttestationByCongressAndType($congressId, $attestationTypeId)
    {
        return AttestationDivers::where('congress_id', '=', $congressId)
            ->where('attestation_type_id', '=', $attestationTypeId)
            ->first();
    }

    public function getAttestationDiversByCongress($congressId)
    {
        return AttestationDivers::with(['type'])
            ->where('congress_id', '=', $congressId)
            ->get();
    }


}