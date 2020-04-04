<?php


namespace App\Services;

use App\Models\SubmissionTheme;
use App\Models\Theme;

class ThemeServices {


    public function getAllThemesByCongress(){

        return Theme::whereHas('congresses')->get();
    
    }
}