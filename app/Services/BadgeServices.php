<?php

namespace App\Services;

use App\Models\Badge;
use Illuminate\Http\Request;
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

    public function validerBadge($congressId, $badgeIdGenerator)
    {
        $badge = new Badge();
        $badge->congress_id = $congressId;
        $badge->badge_id_generator = $badgeIdGenerator;
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
}