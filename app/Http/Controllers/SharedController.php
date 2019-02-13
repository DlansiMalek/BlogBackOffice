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

    public function getFormInputTypes(){
        return $this->sharedServices->getFormInputTypes();
    }

    public function getFile($file_path){
        return response()->file('../storage/app/mail-images/'.$file_path);
    }
}
