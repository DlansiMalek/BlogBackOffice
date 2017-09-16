<?php

namespace App\Services;


use App\Models\City;
use App\Models\Country;

class GeoServices
{
    public function getAllCountries()
    {
        return Country::All();
    }

    public function getAllCities()
    {
        return City::with(['country'])->get();
    }

    public function getCountryById($country_id)
    {
        return Country::find($country_id);
    }

    public function getCitiesByCountry($country_id)
    {
        return City::where('CountryCode', '=', $country_id)->get();
    }
}