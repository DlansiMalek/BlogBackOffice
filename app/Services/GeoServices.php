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

    public function getCityByNameAndCountryCode($name, $countryCode)
    {
        return City::where('name', '=', $name)
            ->where('country_code', '=', $countryCode)
            ->first();
    }

    public function getCongressLocationByCongressId($congressId)
    {
        return Location::where('congress_id', '=', $congressId)
            ->first();
    }

    public function getLocationById($locationId)
    {
        return Location::where('location_id', '=', $locationId)
            ->first();
    }

    public function getCountryByCode($shortname)
    {
        return Country::where('code', '=', $shortname)
            ->first();
    }

    public function getCountryById($country_id)
    {
        return Country::find($country_id);
    }

    public function getCity($countryCode, $cityName)
    {
        $country = $this->getCountryByCode($countryCode);

        if (!$country) {
            $country = $this->addCountry($countryCode);
        }

        $city = $this->getCityByNameAndCountryCode($cityName, $country->alpha3code);

        if (!$city) {
            $city = $this->addCity($cityName, $country->alpha3code);
        }

        return $city;

    }

    private function addCountry($countryCode)
    {
        $country = new Country();

        $country->alpha3code = $countryCode;
        $country->code = $countryCode;
        $country->name = $countryCode;
        $country->save();

        return $country;
    }

    private function addCity($cityName, $alpha3code)
    {
        $city = new City();

        $city->name = $cityName;
        $city->country_code = $alpha3code;
        $city->save();

        return $city;
    }

}