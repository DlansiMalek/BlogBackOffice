<?php


namespace App\Services;

use App\Models\SubmissionTheme;
use App\Models\Theme;

class ThemeServices {


    public function getAllThemes()
    {

        return Theme::all();
    
    }
    
    public function getThemesByCongressId($congressId){
        return Theme::whereHas('congresses',function($query) use ($congressId){
            $query->where('Congress.congress_id','=',$congressId);
         
        })->get();
    }
}