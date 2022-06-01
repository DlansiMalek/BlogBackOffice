<?php

namespace App\Http\Controllers;
use App\Models\Theme;
use App\Services\ThemeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\CongressServices;
class ThemeController extends Controller
{
    protected $themeServices;
    protected $congressServices;


    function __construct(ThemeServices $themeServices,
                         CongressServices $congressServices)
    {
        $this->themeServices=$themeServices;
        $this->congressServices = $congressServices;

    }


    public function getAllThemes($congressId)
    {

        return $this->themeServices->getAllThemes($congressId);
    }

    public  function getThemesByCongressId($congressId)
    {
        return $this->themeServices->getThemesByCongressId($congressId);
    }
    public function addExternalTheme($congressId, Request $request) {
        if (!($congress = $this->congressServices->isExistCongress($congressId))) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $this->themeServices->addExternalTheme($congressId,$request->input('theme'));
            return response()->json(['response' => 'Successfully created'], 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }
}
