<?php


namespace App\Services;

use App\Models\SubmissionTheme;
use App\Models\Theme;

class ThemeServices {


    public function getAllThemes()
    {

        return Theme::all();
    
    }
    
    public function getThemeByCongressIdAndThemeId($themeId,$congressId){
        return Theme::whereHas('congresses',function($query) use ($themeId,$congressId){
            $query->where('Congress.congress_id','=',$congressId);
            $query->where('theme_id','=',$themeId);
        })->first();
    }
}