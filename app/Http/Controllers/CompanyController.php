<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminServices;
use App\Services\CompanyServices;
use App\Services\CongressServices;

class CompanyController extends Controller
{
    protected $adminServices;
    protected $companyServices;
    protected $congressServices;

    function __construct(
        AdminServices $adminServices,
        CompanyServices $companyServices,
        CongressServices $congressServices
    ) {
        $this->adminServices = $adminServices;
        $this->companyServices = $companyServices;
        $this->congressServices = $congressServices;
    }

    public function addCompany(Request $request, $congressId)
    {
        if (!$request->has(['name', 'logo'])) {
            return response()->json(['message' => 'bad request'], 400);
        }
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$congress = $this->congressServices->isExistCongress($congressId)) {
            return response()->json('no congress found', 404);
        }
        $company = $request->has('company_id') ? $this->companyServices->getcompanyById($request->input('company_id')) : null;
        $newCompany = $this->companyServices->addCompany($request, $company, $congressId);
        return response()->json($newCompany);
    }

    public function getAllCompanies($congressId)
    {
        if (!$congress = $this->congressServices->isExistCongress($congressId)) {
            return response()->json('no congress found', 404);
        }
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        $companies = $this->companyServices->getAllCompaniesByCongress($congressId);
        return response()->json($companies);
    }

    public function deleteCompany($company_id)
    {
        if (!$company = $this->companyServices->getcompanyById($company_id)) {
            return response()->json('no congress found', 404);
        }
        $this->companyServices->deleteCompany($company_id);
        return response()->json('deleted successfully', 200);
    }
}
