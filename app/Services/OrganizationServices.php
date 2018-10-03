<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Organization;

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
}