<?php

namespace App\Services;


use App\Models\Country;

class GeoServices
{
    public function getAllCountries()
    {
        return Country::All();
    }

    public function getCountryById($country_id)
    {
        return Country::find($country_id);
    }

}