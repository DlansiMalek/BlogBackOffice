<?php

namespace App\Http\Controllers;

use App\Services\GeoServices;

class GeoController extends Controller
{
    protected $geoServices;

    function __construct(GeoServices $geoServices)
    {
        $this->geoServices = $geoServices;
    }

}
