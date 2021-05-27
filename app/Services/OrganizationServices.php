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
use App\Models\Congress_Organization;
use App\Models\CongressOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class OrganizationServices
{


    public function getAll()
    {
        return Organization::all();
    }

    public function getOrganizationById($organization_id)
    {
        return Organization::with(['CongressOrganization', 'resource', 'admin' => function ($query) {
            $query->select('admin_id','email');
        }])->find($organization_id);
    }

    public function addOrganization(Request $request)
    {
        $organization = new Organization();
        $organization->name = $request->input("name");
        $organization->description = $request->has("description")? $request->input('description') : null;
        $organization->mobile = $request->input("mobile");
        $organization->email = $request->input("email");
        $organization->logo_position = $request->input("logo_position");
        $organization->save();
        return $organization;
    }

    public function editOrganization($oldOrg, $request) {
       
        $oldOrg->name =  $request->input("name");
        $oldOrg->description = $request->has("description")? $request->input('description') : null;
        $oldOrg->mobile = $request->input("mobile");
        $oldOrg->resource_id = $request->input("resource_id");
        $oldOrg->is_sponsor = $request->input("is_sponsor");
        $oldOrg->logo_position = $request->input("logo_position");
        $oldOrg->update();
        return $oldOrg ;
    }

    public function deleteOrganization($organization)
    {
        if ($organization->admin_id) {
            $this->deleteAdminCongress($organization->admin_id);
        }
        return $organization->delete();
    }

    public function deleteAdminCongress($admin_id)
    {
        AdminCongress::where('admin_id', '=', $admin_id)->delete();
    }

    public function deleteCongressOrganization($congressOrganization)
    {
        return $congressOrganization->delete();
    }

    public function getCongressOrganization($congress_id, $organization_id)
    {
        return CongressOrganization::where('congress_id', '=', $congress_id)
        ->where('organization_id', '=', $organization_id)
        ->first();
    }

    public function getOrganizationByAdminId($admin_id)
    {
        return Organization::with(['congress_organization'])->where('admin_id', "=", $admin_id)->first();
    }

    public function getOrganizationByCongressIdAndOrgId($congress_id, $organizationId)
    {
        return CongressOrganization::where('congress_id', '=', $congress_id)
            ->where('organization_id', '=', $organizationId)
            ->first();
    }

    public function getOrganizationByName($name)
    {
        $name = strtolower($name);
        return Organization::whereRaw('lower(name) like (?)', ["{$name}"])
            ->first();
    }

    public function affectOrganizationToCongress($congress_id, $organization_id, $is_sponsor, $banner, $resource_id, $admin_id)
    { 
        $congress_organization = new CongressOrganization();
        $congress_organization->congress_id = $congress_id;
        $congress_organization->organization_id = $organization_id;
        $congress_organization->resource_id = $resource_id;
        $congress_organization->is_sponsor = $is_sponsor;
        $congress_organization->banner = $banner;
        $congress_organization->admin_id = $admin_id;
        $congress_organization->save();

        return $congress_organization;
    }

    public function getOrganizationsByCongressId($congressId)
    {
        return Organization::whereHas('congressOrganization', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        })
            ->with(['admin', 'congressOrganization' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }])
            ->get();
    }

    public function getAllUserByOrganizationId($organizationId, $congressId)
    {
        return User::whereHas('user_congresses', function ($query) use ($organizationId, $congressId) {
            $query->where('congress_id', '=', $congressId);
            $query->where('organization_id', '=', $organizationId);
        })
            ->with(['payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
                'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }])
            ->get();
    }


   public function getOrganizmeByCongressId($congressId,$isLogoPosition) {      
       return Organization::whereHas('congressOrganization',function($query) use($congressId) {
           $query->where('congress_id','=',$congressId);
       })->when($isLogoPosition,function($query) {
            return $query->where('logo_position','!=',NULL);
       })
       ->with('resource')
       ->get();
   }

   public function getSponsorsByCongressId($congressId)
    {
        return Organization::whereHas('congressOrganization', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId)
            ->where('is_sponsor', '=', 1);
        })
            ->with(['resource', 'congressOrganization' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }])
            ->get();
    }
}
