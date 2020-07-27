<?php

namespace App\Http\Controllers;


use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use http\Env\Request;
use Illuminate\Support\Facades\Log;

class SharedController extends Controller
{

    protected $sharedServices;
    protected $userServices;


    function __construct(SharedServices $sharedServices,
                         UserServices $userServices)
    {
        $this->sharedServices = $sharedServices;
        $this->userServices = $userServices;
    }

    public function getAllPrivileges()
    {
        return response()->json($this->sharedServices->getAllPrivileges());
    }

    public function getPrivilegesWithBadges()
    {
        return response()->json($this->sharedServices->getPrivilegesWithBadges());
    }

    public function getAllTypesAttestation()
    {
        return response($this->sharedServices->getAllTypesAttestation());
    }

    public function getAllCountries()
    {
        return response()->json($this->sharedServices->getAllCountries());
    }

    function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function getAllCongressTypes()
    {
        return response()->json($this->sharedServices->getAllCongressTypes());


    }

    public function getAllServices(){
        return $this->sharedServices->getAllServices();
    }

    public function getAllEtablissements(){
        return $this->sharedServices->getAllEtablissements();
    }
}
