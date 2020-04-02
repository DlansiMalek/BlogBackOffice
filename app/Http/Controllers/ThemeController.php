<?php

namespace App\Http\Controllers;
use App\Models\Theme;
use App\Services\ThemeServices;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    private $themeServices;

    function __construct(ThemeServices $themeServices)
    {
        $this->themeServices=$themeServices;
    }

    public function getAllThemes(){
        
        return Theme::All();
    }

    public function getAllThemesByCongress(){
        
        return $this->themeServices->getAllThemesByCongress();
    }
}
