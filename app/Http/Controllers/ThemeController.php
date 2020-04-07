<?php

namespace App\Http\Controllers;
use App\Models\Theme;
use App\Services\ThemeServices;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    protected $themeServices;

    function __construct(ThemeServices $themeServices)
    {
        $this->themeServices=$themeServices;
    }


    public function getAllThemes(){

        return $this->themeServices->getAllThemes();
    }
}
