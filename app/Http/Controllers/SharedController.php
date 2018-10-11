<?php

namespace App\Http\Controllers;


use App\Services\SharedServices;
use Illuminate\Support\Facades\Log;

class SharedController extends Controller
{

    protected $sharedServices;


    function __construct(SharedServices $sharedServices)
    {
        $this->sharedServices = $sharedServices;
    }

    public function getAllGrades()
    {
        return response()->json($this->sharedServices->getAllGrades());
    }

    public function getAllLieux()
    {
        return response()->json($this->sharedServices->getAllLieux());
    }

    public function getAllPrivileges()
    {
        return response()->json($this->sharedServices->getAllPrivileges());
    }

    public function getPrivilegesWithBadges()
    {
        return response()->json($this->sharedServices->getPrivilegesWithBadges());
    }

    public function getPhoto($path)
    {
        Log::info($path);

        $chemin = config('media.congress-logo');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }
}
