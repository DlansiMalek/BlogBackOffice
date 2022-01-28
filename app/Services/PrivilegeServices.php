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

        $this->addPrivilegeConfig($privilege->privilege_id, $congress_id, 1);

        if (!$privilege_base_config = $this->getPrivilegeConfig($priv_reference, $congress_id)) {
            $this->addPrivilegeConfig($priv_reference, $congress_id, 2);
        }
        return $privilege;
    }

    public function addPrivilegeConfig($privilege_id, $congress_id, $status)
    {
        $privilege_config = new PrivilegeConfig();
        $privilege_config->privilege_id = $privilege_id;
        $privilege_config->congress_id = $congress_id;
        $privilege_config->status = $status;
        $privilege_config->save();
        return $privilege_config;
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

    public function getAllPrivilegeCorrespondentsByPrivilege($privilege_id, $congress_id)
    {
        return Privilege::where('priv_reference', '=', $privilege_id)
            ->whereHas('privilegeConfig', function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            })
            ->get();
    }

    public function getPrivilegesDeBase()
    {
        return Privilege::where('priv_reference', '=', null)
            ->get();
    }

    public function getAllPrivilegesCorrespondents($congress_id)
    {
        $privilegeBase = Privilege::where('priv_reference', '=', null)
            ->whereDoesntHave('privilegeConfig', function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id)->where('status', '=', 2);
            })->get()->toArray();

        $newPrivileges = Privilege::where('priv_reference', '!=', null)
            ->join('Privilege_Config', function ($join) use ($congress_id) {
                $join->on('Privilege.privilege_id', '=', 'Privilege_Config.privilege_id')
                    ->where('Privilege_Config.congress_id', '=', $congress_id)
                    ->where('Privilege_Config.status', '=', 1);
            })->get()->toArray();
        $result = array_merge($privilegeBase, $newPrivileges);
        return $result;
    }

    public function getPrivilegesByCongress($congress_id)
    {
        $privilegeBase = Privilege::where('priv_reference', '=', null)
            ->with(['privilege_menu_children' => function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            },
                'privilegeConfig' => function ($query) use ($congress_id) {
                    $query->where('congress_id', '=', $congress_id);
                }])->get()->toArray();

        $otherPrivileges = Privilege::where('priv_reference', '!=', null)
            ->whereHas('privilegeConfig', function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            })->with(['privilege_menu_children' => function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            }
                , 'privilegeConfig', 'privilege'])
            ->get()->toArray();
        $result = array_merge($privilegeBase, $otherPrivileges);
        return $result;
    }

    public function affectPrivilegeToAdmin($privilegeId, $adminId, $congress_id, $organization_id = null)
    {
        $admin_congress = new AdminCongress();
        $admin_congress->admin_id = $adminId;
        $admin_congress->privilege_id = $privilegeId;
        $admin_congress->congress_id = $congress_id;
        $admin_congress->organization_id = $organization_id;
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

    public function deletePrivilege($privilege_id, $congress_id, $privilege)
    {
        $this->deletePrivilegeConfig($privilege_id, $congress_id);
        $privilege->delete();
        $privileges = $this->getAllPrivilegeCorrespondentsByPrivilege($privilege->priv_reference, $congress_id);
        $privileges->toArray();
        if (count($privileges) == 0) {
            $this->deletePrivilegeConfig($privilege->priv_reference, $congress_id, 0);
        }
    }

    public function deletePrivilegeConfig($privilege_id, $congress_id, $status = null)
    {
        if ($status != null) {
            return PrivilegeConfig::where('privilege_id', '=', $privilege_id)
                ->where('congress_id', '=', $congress_id)
                ->where('status', '=', $status)
                ->delete();
        } else {
            return PrivilegeConfig::where('privilege_id', '=', $privilege_id)
                ->where('congress_id', '=', $congress_id)
                ->delete();
        }
    }


    public function hidePrivilege($congress_id, $id_privilege)
    {
        if ($privilege_config = $this->getPrivilegeConfig($id_privilege, $congress_id)) {
            $privilege_config->status = 2;
            $privilege_config->update();
        } else {
            $privilege_config = new PrivilegeConfig();
            $privilege_config->privilege_id = $id_privilege;
            $privilege_config->congress_id = $congress_id;
            $privilege_config->status = 2;
            $privilege_config->save();
        }
        return $privilege_config;
    }

    public function activatePrivilege($congress_id, $privilege_id)
    {
        $privilege_config = $this->getPrivilegeConfig($privilege_id, $congress_id);
        $privilege_config->status = 1;
        $privilege_config->update();
        return $privilege_config;
    }


}
