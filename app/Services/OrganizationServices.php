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


class OrganizationServices
{
    public function getAll()
    {
        return Organization::all();
    }

    public function getOrganizationById($organization_id, $congress_id = null)
    {
        return Organization::with(['admin','stands'])
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

    public function getOrganizationByNameAndEmailInCongress($name, $email, $congressId)
    {
        $email = strtolower($email);
        return Organization::whereRaw('lower(name) like (?)', ["{$name}"])->whereRaw('lower(email) like (?)', ["{$email}"])->where('congress_id', '=', $congressId)->first();
    }

    public function getOrganizationsByCongressId($congressId, $admin_email = null, $privilege_id = null)
    {
        return  Organization::with(['admin'])->where('congress_id', '=', $congressId)->where(function ($query) use ($admin_email, $privilege_id) {
            if ($admin_email && $privilege_id == config('privilege.Organisme')) $query->where('email', '=', $admin_email);
        })->get();
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
    public function addOrganizationFromExcel($organization,$organzationData,$congressId, $admin)
    {
        if (!$organization) {
            $organization = new Organization();
        }
        $organization->name          = $organzationData['organization_name'];
        $organization->description   = isset($organzationData["organization_description"]) ? $organzationData['organization_description'] : null;
        $organization->mobile        = isset($organzationData['organization_phone']) ? $organzationData['organization_phone'] : null ;
        $organization->email         = isset($organzationData['organization_email']) ? $organzationData['organization_email'] : null;
        $organization->website_link  = isset($organzationData['organization_website']) ? $organzationData['organization_website'] : null;
        $organization->twitter_link  = isset($organzationData['organization_twitter']) ? $organzationData['organization_twitter'] : null;
        $organization->linkedin_link = isset($organzationData['organization_linkendin']) ? $organzationData['organization_linkendin'] : null;
        $organization->insta_link    = isset($organzationData['organization_insta']) ? $organzationData['organization_insta'] : null;
        $organization->fb_link       = isset($organzationData['organization_fb']) ? $organzationData['organization_fb'] : null ;
        $organization->congress_id   = $congressId;
        $organization->admin_id      = $admin ? $admin->admin_id : null;
        $organization->save();
        return $organization;
    }
    public function getOrganizationByNameAndCongress($organization_name, $congress_id)
    {
        return Organization::with(['admin'])
            ->where('congress_id', '=', $congress_id)
            ->whereRaw('lower(name) like (?)', ["{$organization_name}"])
            ->first();
    }
}
