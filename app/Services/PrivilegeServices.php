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

class PrivilegeServices
{
    public function affectPrivilegeToAdmin($privilegeId, $adminId, $congress_id)
    {
        $admin_congress = new AdminCongress();

        $admin_congress->admin_id = $adminId;
        $admin_congress->privilege_id = $privilegeId;
        $admin_congress->congress_id = $congress_id;

        $admin_congress->save();

        return $admin_congress;
    }

    public function checkIfHasPrivilege($adminId,$congress_id)
    {
        return AdminCongress::where('admin_id','=',$adminId)
            ->where('congress_id','=',$congress_id)
            ->first();
    }

}