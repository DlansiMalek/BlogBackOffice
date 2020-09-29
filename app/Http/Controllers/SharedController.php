<?php

namespace App\Http\Controllers;

use App\Services\CommunicationTypeService;
use App\Services\SharedServices;
use App\Services\CongressServices;
use App\Services\UserServices;
use App\Services\PrivilegeServices;
use App\Models\Privilege;
use App\Models\Congress;
use Illuminate\Http\Request;

class SharedController extends Controller
{

    protected $sharedServices;
    protected $userServices;
    protected $congressServices;
    protected $privilegeServices;
    protected $communicationTypeService;

    function __construct(SharedServices $sharedServices,
                         UserServices $userServices,
                         PrivilegeServices $privilegeServices,
                         CommunicationTypeService $communicationTypeService,
                         CongressServices $congressServices)
    {
        $this->sharedServices = $sharedServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->privilegeServices = $privilegeServices;
        $this->communicationTypeService = $communicationTypeService;
    }



    public function getAllPrivileges()
    {
        return response()->json($this->sharedServices->getAllPrivileges()); 
    }

    public function getCorPrivileges($congress_id)
    {
        return response()->json($this->sharedServices->getCorPrivileges($congress_id)); 
    }

    public function addPrivilege(Request $request)
     {    
         
    
        $this->privilegeServices->addPrivilege(
            $request->input('name'),
            $request->input('internal'),
            $request->input('priv_reference'),
            $request->input('congress_id')
             );
            $privCors = $this->sharedServices->getCorPrivileges( $request->input('priv_reference'),$request->input('congress_id'));
        return response()->json(['response' => 'privilege added','privs' => $privCors], 200);
            
     
    } 


     public function delete($id_privilege)
     { $privilege = $this->privilegeServices->checkValidPrivilege($id_privilege,$congress_id);
        $privilege->delete();
        return response()->json(['response' => 'privilege deleted'],200);
     }

     public function checkValidPrivilege ($id_privilege,$congress_id)
     {   return $this->privilegeServices->checkValidPrivilege($id_privilege,$congress_id);
         
     }

     public function hidePrivilege($id_privilege,$congress_id)
    {  
        $privilege= $this->privilegeServices->checkValidPrivilege($id_privilege,$congress_id);
        $privilege->internal = -1;
        $privilege->update();
        $privs = $this->sharedServices->getPrivilegesWithBadges();
        return response()->json(['response' => 'internal changed','privs' => $privs],200);
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
