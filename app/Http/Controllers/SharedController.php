<?php

namespace App\Http\Controllers;

use App\Services\CommunicationTypeService;
use App\Services\SharedServices;
use App\Services\UserServices;

class SharedController extends Controller
{

    protected $sharedServices;
    protected $userServices;
    protected $communicationTypeService;

    function __construct(SharedServices $sharedServices,
                         UserServices $userServices,
                         CommunicationTypeService $communicationTypeService)
    {
        $this->sharedServices = $sharedServices;
        $this->userServices = $userServices;
        $this->communicationTypeService = $communicationTypeService;
    }

    public function getAllPrivileges()
    {
        return response()->json($this->sharedServices->getAllPrivileges());
    }

    public function getPrivilegesWithBadges()
    {
        return response()->json($this->sharedServices->getPrivilegesWithBadges());
    }

    public function getAllCommunicationTypes()
    {
        return $this->communicationTypeService->getAllCommunicationType();
    }

    public function getRecuPaiement($path)
    {
        $chemin = config('media.payement-user-recu');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
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

    public function getAllServices()
    {
        return $this->sharedServices->getAllServices();
    }

    public function getAllEtablissements()
    {
        return $this->sharedServices->getAllEtablissements();
    }

    public function encrypt($password)
    {
        return bcrypt($password);
    }
}
