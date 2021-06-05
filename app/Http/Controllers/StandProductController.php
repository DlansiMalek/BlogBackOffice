<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StandServices;
use App\Services\StandProductServices;
use Illuminate\Support\Facades\Log;

class StandProductController extends Controller
{
    protected $standServices;
    protected $standProductServices;

    function __construct(StandServices $standServices, StandProductServices $standProductServices)
    {
        $this->standServices = $standServices;
        $this->standProductServices = $standProductServices;
    }

    function addStandProduct(Request $request)
    {

        $standproduct = $this->standProductServices->addStandProduct(
            $request->input('name'),
            $request->input('stand_id'),
            $request->input('description'),
            $request->input('main_img'),
            $request->input('brochure_file'),
        ); 
			$resources = $request->has('product_resource_paths') ? $request->input('product_resource_paths') : [];
			$this->standProductServices->saveResourceStandProduct($resources, $standproduct->stand_product_id);
			return response()->json('Standproduct added', 200);
    }

    public function editStandProduct($congress_id, $stand_id, $standproduct_id,  Request $request)
    {

        if (!$Stand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        if (!$oldStandProduct = $this->standProductServices->getStandProductById($standproduct_id)) {
            return response()->json(['response' => 'product not found', 404]);
        }

        $standproduct = $this->standProductServices->editStandProduct(
            $oldStandProduct,
            $request->input('name'),
            $request->input('description'),
            $request->input('main_img'),
            $request->input('brochure_file')
        );
     
        return response()->json($standproduct, 200);
    }

    public function getStandproducts($congress_id, $stand_id)
    {
        if (!$Stand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        $standproducts = $this->standProductServices->getStandproducts($stand_id);
        return response()->json($standproducts, 200);
    }

    public function deleteStandproduct($stand_product_id)
    {
        if (!$standproduct = $this->standProductServices->getStandProductById($stand_product_id)) {
            return response()->json('no such product found', 404);
        }
        $standproduct->delete();
        return response()->json(['response' => 'product deleted'], 200);
    }

    public function getStandProductById($congressId, $standproduct_id)
    {
        return $this->standProductServices->getStandProductById($standproduct_id);
    }
}
