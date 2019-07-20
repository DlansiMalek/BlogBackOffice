<?php

namespace App\Services;


use App\Models\City;
use App\Models\Country;
use App\Models\Location;

class GeoServices
{
    public function getAllCountries()
    {
        return Country::All();
    }

    public function getCityByNameAndCountryCode($name,$countryCode) {
        return City::where('name','=',$name)
            ->where('country_code','=',$countryCode)
            ->first();
    }
    public function getCongressLocationByCongressId($congressId) {
        return Location::leftJoin('congress', 'Location.location_id', '=', 'congress.location_id')
            ->where('congress_id','=',$congressId)
            ->first();
    }

    public function getLocationById($locationId) {
        return Location::where('location_id','=',$locationId)
            ->first();
    }
    public function getCountryByCode($shortname) {
        return Country::where('code','=',$shortname)
            ->first();
    }
    public function getCountryById($country_id)
    {
        return Country::find($country_id);
    }

}