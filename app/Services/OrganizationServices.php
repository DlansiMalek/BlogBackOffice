<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;

use App\Models\AdminCongress;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganizationServices
{
    public function getAll()
    {
        return Organization::all();
    }

    public function getOrganizationById($organization_id, $congress_id = null)
    {
        return Organization::with(['admin'])
            ->find($organization_id);
    }

    public function addOrganization($organization, $congressId, Request $request)
    {
        if (!$organization)
          $organization = new Organization();
        
        $organization->congress_id   = $congressId;
        $organization->name          = $request->input("name");
        $organization->admin_id      = $request->input("admin_id");
        $organization->description   = $request->input('description');
        $organization->mobile        = $request->input("mobile");
        $organization->email         = $request->input("email");
        $organization->banner        = $request->input("banner");
        $organization->logo          = $request->input("logo");
        $organization->website_link  = $request->input("website_link");
        $organization->twitter_link  = $request->input("twitter_link");
        $organization->linkedin_link = $request->input("linkedin_link");
        $organization->fb_link       = $request->input("fb_link");
        $organization->insta_link    = $request->input("insta_link");
        $organization->is_sponsor    = $request->input("is_sponsor");
        
        $organization->save();
        return $organization;
    }

    public function deleteOrganization($organization)
    {
        return $organization->delete();
    }

    public function deleteAdminCongress($admin_id)
    {
        AdminCongress::where('admin_id', '=', $admin_id)->delete();
    }

    public function getOrganizationByAdminId($admin_id)
    {
        return Organization::where('admin_id', "=", $admin_id)->first();
    }

    public function getOrganizationByEmail($email)
    {
        $email = strtolower($email);
        return Organization::whereRaw('lower(email) like (?)', ["{$email}"])
            ->first();
    }

    public function getOrganizationsByCongressId($congressId)
    {
        return Organization::with(['admin'])->where('congress_id', '=', $congressId)
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

   public function getSponsorsByCongressId($congressId)
    {
        return Organization::with(['resource'])
            ->where('is_sponsor', '=', 1)
            ->where('congress_id', '=', $congressId)
            ->get();
    }
    public function addOrganizationFromExcel($organzationData)
    {
        $organization = new Organization();
        $organization->name=$organzationData['name'];
        $organization->description=$organzationData['description'];
        $organization->mobile=$organzationData['mobile'];
        $organization->email=$organzationData['email'];
        $organization->website_link=$organzationData['website_link'];
        $organization->twitter_link=$organzationData['twitter_link'];
        $organization->linkedin_link=$organzationData['linkedin_link'];
        $organization->insta_link=$organzationData['insta_link'];
        $organization->fb_link=$organzationData['fb_link'];
        $organization->fb_link=$organzationData['fb_link'];
        $organization->admin_id=$organzationData['admin_id'];
        $organization->save();
        return $organization;
    }
    public function getOrganizationByNameAndCongress($organization_name, $congress_id)
    {
        return Organization::with(['admin'])
            ->where('congress_id', '=', $congress_id)->where('name', '=', $organization_name)
            ->first();
    }
}
