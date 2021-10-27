<?php

namespace App\Services;

use App\Models\FMenu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class FMenuServices
{

    public function editFMenu($fmenu, $congress_id, $fetched = null)
    {
        if ($fetched) {
            $fetched->key = $fmenu['key'];
            $fetched->fr_label = $fmenu['fr_label'];
            $fetched->en_label = $fmenu['en_label'];
            $fetched->is_visible = $fmenu['is_visible'];
            $fetched->rank = $fmenu['rank'];
            $fetched->save();
            return $fetched;
        }
        $newfmenu = new FMenu();
        $newfmenu->key = $fmenu['key'];
        $newfmenu->fr_label = $fmenu['fr_label'];
        $newfmenu->en_label = $fmenu['en_label'];
        $newfmenu->is_visible = $fmenu['is_visible'];
        $newfmenu->rank = $fmenu['rank'];
        $newfmenu->congress_id =  $congress_id;
        $newfmenu->save();
        return $newfmenu;
    }

    public function getFMenuById($fmenu_id, $congress_id)
    {
        return FMenu::where('FMenu_id', '=', $fmenu_id)->where('congress_id', '=', $congress_id)->first();
    }
}
