<?php


namespace App\Services;

use App\Models\SubmissionTheme;
use App\Models\Theme;

class ThemeServices {


    public function getAllThemesByCongress(){

        return Theme::whereHas('congresses')->get();
    
    }
    
    public function getThemeByCongressIdAndThemeId($themeId,$congressId){
        return Theme::whereHas('congresses',function($query) use ($themeId,$congressId){
            $query->where('Congress.congress_id','=',$congressId);
            $query->where('theme_id','=',$themeId);
        })->first();
    }
}