<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Admin;
use App\Models\Admin_Privilege;
use App\Models\AdminCongress;
use App\Models\Privilege;

class PrivilegeServices
{

    public function addPrivilege($name,$priv_reference,$internal,$congress_id) {
        $privilege = new Privilege();
        $privilege->name =  $name;
        $privilege->internal =  $internal;
        $privilege->priv_reference = $priv_reference;
        $privilege->congress_id = $congress_id;
        $privilege->save();
        return $privilege;
    }



    public function affectPrivilegeToAdmin($privilegeId, $adminId, $congress_id)
    {
        $admin_congress = new AdminCongress();

        $admin_congress->admin_id = $adminId;
        $admin_congress->privilege_id = $privilegeId;
        $admin_congress->congress_id = $congress_id;

        $admin_congress->save();

        return $admin_congress;
    }

    public function editPrivilege($privilegeId, $adminId, $congress_id) {
        return AdminCongress::where('admin_id','=',$adminId)
            ->where('congress_id','=',$congress_id)
            ->update(['privilege_id' => $privilegeId]);

    }



    public function checkIfAdminOfCongress($adminId,$congress_id)
    {
        return AdminCongress::where('admin_id','=',$adminId)
            ->where('congress_id','=',$congress_id)
            ->first();
    }

    public function deleteAdminCongressByIds($admincongress)
    {
        $admincongress->delete();
    }

    public function checkValidPrivilege($privilege_id, $congress_id) {
        return Privilege::where('privilege_id','=', $privilege_id)->where('congress_id','=',$congress_id)->first();

    }


}