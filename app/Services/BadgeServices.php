<?php

namespace App\Services;

use App\Models\Access_Presence;
use App\Models\Attestation;
use App\Models\Attestation_Access;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function validerBadge($congressId, $badgeIdGenerator, $organiser)
    {
        $badge = new Badge();
        $badge->congress_id = $congressId;
        if ($organiser == 1) {
            $badge->badge_org_id_generator = $badgeIdGenerator;
        } else {
            $badge->badge_id_generator = $badgeIdGenerator;
        }
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
        return Attestation_Access::where('access_id', '=', $accessId)
            ->first();
    }

    public function validerAttestationAccess($accessId, $attesationIdGenerator)
    {
        $attestationAccess = new Attestation_Access();
        $attestationAccess->access_id = $accessId;
        $attestationAccess->attestation_generator_id = $attesationIdGenerator;
        $attestationAccess->save();
        return $attestationAccess;
    }

    public function getAttestationEnabled($user_id, $access)
    {
        $presenceAccess = Access_Presence::where('user_id', '=', $user_id)
            ->where('access_id', '=', $access->access_id)
            ->orderBy('enter_time', 'asc')
            ->get();
        if (sizeof($presenceAccess) == 0) {
            return 0;
        }

        $startCongress = $access->start_date;
        $endCongress = date('Y-m-d H:i:s', strtotime($startCongress . ' + ' . $access->duration . ' minute'));

        $calculatedTime = 0;
        foreach ($presenceAccess as $item) {
            if ($item->leave_time == null) {
                $diff = Utils::diffMinutes($item->enter_time, $endCongress);
            } else {
                $diff = Utils::diffMinutes($item->enter_time, $item->leave_time);
            }
            $calculatedTime += $diff;
        }
        if ($diff >= $access->seuil) {
            return 1;
        } else {
            return 2;
        }
    }


}