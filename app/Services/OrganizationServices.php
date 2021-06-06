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
        return Organization::where('is_sponsor', '=', 1)
            ->where('congress_id', '=', $congressId)
            ->get();
    }
    public function addOrganizationFromExcel($organization,$organzationData,$congressId,$adminId=null)
    {
        if($organization==null)
        {
        $organization = new Organization();
        }
        $organization->name=$organzationData['organization_name'];
        if(array_key_exists("organization_description", $organzationData))
        $organization->description=$organzationData['organization_description'];
        if(array_key_exists("organization_phone", $organzationData))
        $organization->mobile=$organzationData['organization_phone'];
        if(array_key_exists("organization_email", $organzationData))
        $organization->email=$organzationData['organization_email'];
        if(array_key_exists("organization_website", $organzationData))
        $organization->website_link=$organzationData['organization_website'];
        if(array_key_exists("organization_twitter", $organzationData))
        $organization->twitter_link=$organzationData['organization_twitter'];
        if(array_key_exists("organization_linkendin", $organzationData))
        $organization->linkedin_link=$organzationData['organization_linkendin'];
        if(array_key_exists("organization_insta", $organzationData))
        $organization->insta_link=$organzationData['organization_insta'];
        if(array_key_exists("organization_fb", $organzationData))
        $organization->fb_link=$organzationData['organization_fb'];
        $organization->congress_id =$congressId;
        $organization->admin_id =$adminId;
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
