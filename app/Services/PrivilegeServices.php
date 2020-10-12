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
use App\Models\PrivilegeConfig;

class PrivilegeServices
{

    public function addPrivilege($name, $priv_reference, $congress_id)
    {
        $privilege = new Privilege();
        $privilege->name = $name;
        $privilege->priv_reference = $priv_reference;
        $privilege->save();

        $privilege_config = new PrivilegeConfig();
        $privilege_config->privilege_id = $privilege->privilege_id;
        $privilege_config->congress_id = $congress_id;
        $privilege_config->status = 1;
        $privilege_config->save();

        if (!$privilege_base_config = $this->getPrivilegeConfig($priv_reference, $congress_id)) {
            $privilege_base_config = new PrivilegeConfig();
            $privilege_base_config->privilege_id = $priv_reference;
            $privilege_base_config->congress_id = $congress_id;
            $privilege_base_config->status = 0;
            $privilege_base_config->save();
        }
        return $privilege;
    }

    public function getPrivilegeConfig($privilege_id, $congress_id)
    {
        return PrivilegeConfig::where('privilege_id', '=', $privilege_id)
            ->where('congress_id', '=', $congress_id)->first();
    }

    public function getPrivilegeById($id_privilege)
    {
        return Privilege::where('privilege_id', '=', $id_privilege)
            ->first();
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

    public function editPrivilege($privilegeId, $adminId, $congress_id)
    {
        return AdminCongress::where('admin_id', '=', $adminId)
            ->where('congress_id', '=', $congress_id)
            ->update(['privilege_id' => $privilegeId]);

    }


    public function checkIfAdminOfCongress($adminId, $congress_id)
    {
        return AdminCongress::where('admin_id', '=', $adminId)
            ->where('congress_id', '=', $congress_id)
            ->first();
    }

    public function deleteAdminCongressByIds($admincongress)
    {
        $admincongress->delete();
    }

    public function checkValidPrivilege($privilege_id)
    {
        return Privilege::where('privilege_id', '=', $privilege_id)
            ->where('priv_reference', '!=', null)
            ->first();
    }

    public function deletePrivilege($privilege_id, $congress_id, $privilege)
    {
        $privilege_config = $this->getPrivilegeConfig($privilege_id, $congress_id);
        $privilege_config->delete();
        $privilege->delete();
        $privileges = Privilege::where('priv_reference', '=', $privilege->priv_reference)
            ->get()->toArray();
        if (count($privileges) == 0) {
            $privilege_base_config = PrivilegeConfig::where('privilege_id', '=', $privilege_id)
                ->where('congress_id', '=', $congress_id)
                ->where('status', '=', 0)->first();
            $privilege_base_config->delete();
        }
    }

    public function hidePrivilege($congress_id, $id_privilege)
    {
        $privilege_config = new PrivilegeConfig();
        $privilege_config->privilege_id = $id_privilege;
        $privilege_config->congress_id = $congress_id;
        $privilege_config->status = 2;
        $privilege_config->save();
        return $privilege_config;
    }


}
