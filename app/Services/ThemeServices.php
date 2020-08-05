<?php


namespace App\Services;

use App\Models\CongressTheme;
use App\Models\Theme;
use Composer\Util\TlsHelper;

class ThemeServices {


    public function getAllThemes($congressId) {
        return Theme::where('external', '=', 0)
            ->orWhere('external', '=', $congressId)->get();
    }

    public function getThemesByCongressId($congressId){
        return Theme::whereHas('congresses',function($query) use ($congressId){
            $query->where('Congress.congress_id','=',$congressId);

        })->get();
    }
    public function addExternalTheme($congressId, $externalTheme) {
        $theme  = new Theme();
        $theme->label = $externalTheme['label'];
        $theme->external = $congressId;
        $theme->description = $externalTheme['description'];
        $theme->save();
    }
}
