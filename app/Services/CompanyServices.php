<?php

namespace App\Services;
use App\Models\Company;

class CompanyServices
{
    public function getAllCompaniesByCongress($congress_id)
    {
        return Company::where('congress_id', '=', $congress_id)->get();
    }

    public function addCompany($request, $company, $congressId)
    {
        if (!$company) {
            $company = new Company();
        }
        $company->name = $request->input('name');
        $company->description = $request->input('description');
        $company->logo = $request->input('logo');
        $company->congress_id = $congressId;
        $company->save();
        return $company;
    }

    public function getcompanyById($company_id)
    {
        return Company::where('company_id', '=', $company_id)->first();
    }

    public function deleteCompany($company_id)
    {
        return Company::where('company_id', '=', $company_id)->delete();
    }
}