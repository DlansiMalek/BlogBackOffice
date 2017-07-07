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

    function getAllCountries()
    {
        return $this->geoServices->getAllCountries();
    }

    function getAllCities()
    {
        return $this->geoServices->getAllCities();
    }

    function getCitiesByCountry($country_id)
    {
        $country = $this->geoServices->getCountryById($country_id);
        if (!$country) {
            return response()->json(['response' => 'country not found'], 404);
        }
        return $this->geoServices->getCitiesByCountry($country_id);
    }
}
