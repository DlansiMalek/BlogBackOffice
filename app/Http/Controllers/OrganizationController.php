<?php

namespace App\Http\Controllers;


use App\Services\OrganizationServices;

class OrganizationController extends Controller
{

    protected $organizationServices;


    function __construct(OrganizationServices $organizationServices)
    {
        $this->organizationServices = $organizationServices;
    }

}
