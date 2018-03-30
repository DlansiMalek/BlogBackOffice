<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Admin_Privilege;
use App\Models\Privilege;

class PrivilegeServices
{
    public function affectPrivilegeToAdmin($privilegeId, $adminId)
    {
        if ($admin_priv = $this->checkIfHasPrivilege($privilegeId, $adminId)) {
            return $admin_priv;
        }
        $admin_priv = new Admin_Privilege();

        $admin_priv->admin_id = $adminId;
        $admin_priv->privilege_id = $privilegeId;

        $admin_priv->save();

        return $admin_priv;
    }

    public function checkIfHasPrivilege($privilegeId, $adminId)
    {
        return Admin_Privilege::where("admin_id", "=", $adminId)
            ->where("privilege_id", "=", $privilegeId)
            ->first();
    }

}