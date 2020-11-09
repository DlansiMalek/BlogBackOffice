<?php

namespace App\Services;


use App\Models\AccessPresence;
use App\Models\Attestation;
use App\Models\AttestationAccess;
use App\Models\AttestationDivers;
use App\Models\Badge;
use App\Models\BadgeParams;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class BadgeServices
{

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
        $badge->enable = 1;
        $badge->num_downloaded = 0;
        $badge->save();

        return $badge;
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

    public function getAttestationByCongressAndAccess($accessId, $privilegeId)
    {
        return AttestationAccess::where('access_id', '=', $accessId)
            ->where('privilege_id', '=', $privilegeId)
            ->first();
    }

    public function validerAttestationAccess($accessId, $privilegeId, $attesationIdGenerator)
    {
        $attestationAccess = new AttestationAccess();
        $attestationAccess->access_id = $accessId;
        $attestationAccess->privilege_id = $privilegeId;
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

    public function getBadgeByCongressAndPrivilegeBadgeAndIdGenerator($congressId, $privilegeId, $badgeIdGenerator)
    {
        return Badge::with(['privilege:privilege_id,name',
            'badge_param:badge_id,key'])->where('congress_id', '=', $congressId)
            ->where('privilege_id', '=', $privilegeId)->where('badge_id_generator', '=', $badgeIdGenerator)
            ->first();
    }

    public function updateOrCreateBadgeParams($badge_id, $keys, $update)
    {
        if ($update) {
            BadgeParams::where('badge_id', '=', $badge_id)->delete();
        }
        foreach ($keys as $key) {
            $badgeParam = new BadgeParams();
            $badgeParam->badge_id = $badge_id;
            $badgeParam->key = $key;
            $badgeParam->save();
        }

    }


    public function getBadgesByCongressAndPrivilege($congressId, $privilegeId)
    {
        return Badge::where('congress_id', '=', $congressId)
            ->where('privilege_id', '=', $privilegeId)
            ->get();
    }


    public function saveAttestationsInPublic(array $request)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST',
            UrlUtils::getUrlBadge() . '/badge/generateParticipants/multiple', [
                'json' => [
                    'participants' => $request
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

    public function getBadgesByCongress($congressId)
    {
        if (Badge::where('congress_id', '=', $congressId)->get()) {
            $badges = Badge::with(['privilege:privilege_id,name',
                'badge_param:badge_id,key'])
                ->where('congress_id', '=', $congressId)->orderBy('privilege_id')->get();
            $badgesToRender = $badges->map(function ($submission) {

                return collect($submission->toArray())
                    ->only(['badge_id', 'privilege', 'congress_id', 'badge_id_generator', 'enable', 'badge_param', 'created_at'])
                    ->all();
            });
            return $badgesToRender;
        }

        return [];
    }


    public function activateBadgeByCongressByPriviledge($badges, $badgeIdGenerator)
    {
        foreach ($badges as $b) {
            if ($b->badge_id_generator == $badgeIdGenerator) {
                $b->enable = 1;
                $b->update();
            } else {
                $b->enable = 0;
                $b->update();
            }
        }
        return "activated successfully";
    }

    public function getBadgeByIdGenerator($badgeIdGenerator)
    {
        return Badge::where('badge_id_generator', '=', $badgeIdGenerator)->first();
    }

    public function getBadgeByCongress($congressId, $badgeId)
    {
        return Badge::where('congress_id', '=', $congressId)->where('badge_id', '=', $badgeId)
            ->first();
    }

    public function deleteBadge($badgeId)
    {
        Badge::where('badge_id', '=', $badgeId)->delete();
        BadgeParams::where('badge_id', '=', $badgeId)->delete();
    }
}
