<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\SharedServices;
use Illuminate\Http\Request;
use App\Services\PrivilegeServices;


class PrivilegeController extends Controller
{
    protected $privilegeServices;
    protected $adminServices;
    protected $sharedServices;

    function __construct(PrivilegeServices $privilegeServices,
                         AdminServices $adminServices,
                         SharedServices $sharedServices)
    {
        $this->privilegeServices = $privilegeServices;
        $this->adminServices = $adminServices;
        $this->sharedServices = $sharedServices;
    }

    public function getAllPrivilegesCorrespondents($congress_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin not found'], 404);
        }
        return response()->json($this->privilegeServices->getAllPrivilegesCorrespondents($congress_id));
    }

    public function addPrivilege(Request $request)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin not found'], 404);
        }
        $privilege = $this->privilegeServices->addPrivilege(
            $request->input('name'),
            $request->input('priv_reference'),
            $request->input('congress_id')
        );
        return response()->json(['response' => $privilege], 200);
    }


    public function deletePrivilege($congress_id, $id_privilege)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin not found'], 404);
        }
        if (!$privilege = $this->privilegeServices->getPrivilegeById($id_privilege)) {
            return response()->json(['response' => 'Privilege not found'],404);
        }
        $this->privilegeServices->deletePrivilege($id_privilege, $congress_id, $privilege);
        return response()->json(['response' => 'deleted successfully!'],200);

    }

    public function getPrivilegeById ($id_privilege)
    {
        if (!$loggedAdmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin not found'], 404);
        }
        return $this->privilegeServices->getPrivilegeById($id_privilege);
    }

    public function hidePrivilege($congress_id, $id_privilege)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin not found'], 404);
        }
        $privilege = $this->privilegeServices->getPrivilegeById($id_privilege);
        $this->privilegeServices->hidePrivilege($congress_id, $id_privilege);
        if ($privilege->priv_reference == null) {
            $privileges = $this->privilegeServices->getAllPrivilegeCorrespondentsByPrivilege($id_privilege, $congress_id);
            foreach ($privileges as $priv)
            {
                $this->privilegeServices->hidePrivilege($congress_id, $priv->privilege_id);
            }
        }
        return response()->json(['response' => 'hided successfully!'],200);
    }

    public function getPrivilegesDeBase($congress_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $privileges = $this->privilegeServices->getPrivilegesDeBase();
        return response()->json(['privileges' => $privileges]);
    }

    public function getPrivilegesByCongress($congress_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        return response()->json($this->privilegeServices->getPrivilegesByCongress($congress_id));
    }

    public function activatePrivilege($congress_id, $privilege_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        return response()->json($this->privilegeServices->activatePrivilege($congress_id, $privilege_id));
    }

}
