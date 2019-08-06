<?php

namespace App\Http\Controllers;


use App\Services\SharedServices;

class SharedController extends Controller
{

    protected $sharedServices;


    function __construct(SharedServices $sharedServices)
    {
        $this->sharedServices = $sharedServices;
    }

    public function getAllPrivileges()
    {
        return response()->json($this->sharedServices->getAllPrivileges());
    }

    public function getPrivilegesWithBadges()
    {
        return response()->json($this->sharedServices->getPrivilegesWithBadges());
    }

    public function getLogoCongress($path)
    {
        $chemin = config('media.congress-logo');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }

    public function getBannerCongress($path)
    {
        $chemin = config('media.congress-banner');
        return response()->download(storage_path('app/congress-banner/' . $chemin . "/" . $path));
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

    public function getFile($file_path)
    {
        return response()->file('../storage/app/mail-images/' . $file_path);
    }

    public function encrypt($password)
    {
        return bcrypt($password);
    }
    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
