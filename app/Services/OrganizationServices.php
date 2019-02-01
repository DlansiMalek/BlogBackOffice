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
use App\Models\Congress_Organization;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationServices
{


    public function getAll()
    {
        return Organization::all();
    }

    public function getOrganizationById($labId)
    {
        return Organization::find($labId);
    }

    public function addOrganization(Request $request,$congress_id, $admin_id)
    {
        $organization = new Organization();
        $organization->email = $request->input("email");
        $organization->name = $request->input("name");
        $organization->description = $request->input("description");
        $organization->mobile = $request->input("mobile");
        $organization->save();

        $congress_organization = new Congress_Organization();
        $congress_organization->congress_id = $congress_id;
        $congress_organization->organization_id = $organization->organization_id;
        $congress_organization->montant = 0;

        $congress_organization->save();

        $admin = new Admin();
        $admin->name = $request->input("name");
        $admin->email = $request->input("email");
        $admin->mobile = $request->input("mobile");

        $admin->responsible = $admin_id;

        $password = str_random(8);
        $admin->passwordDecrypt = $password;
        $admin->password = bcrypt($password);

        $admin->save();

        $admin_priv = new Admin_Privilege();

        $admin_priv->admin_id = $admin->admin_id;
        $admin_priv->privilege_id = 7;

        $admin_priv->save();

        return $organization;
    }
}