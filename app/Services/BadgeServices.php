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

    public function validerBadge(Request $request, $congressId)
    {
        if (!$badge = $this->getBadgeByCongress($congressId)) {
            $badge = new Badge();
        }
        $badge->img_name = $request->input("img_name");
        $badge->qr_code_choice = $request->input("qr_code_choice");
        $badge->text_choice = $request->input("text_choice");
        $badge->congress_id = $congressId;
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