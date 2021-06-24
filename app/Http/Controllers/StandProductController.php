<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StandServices;
use App\Services\StandProductServices;
use App\Services\CongressServices;
use Illuminate\Support\Facades\Log;

class StandProductController extends Controller
{
    protected $standServices;
    protected $standProductServices;
    protected $congressServices;

    function __construct(StandServices $standServices, StandProductServices $standProductServices, CongressServices $congressServices)
    {
        $this->standServices = $standServices;
        $this->standProductServices = $standProductServices;
        $this->congressServices = $congressServices;
    }

    function addStandProduct(Request $request)
    {

        $standproduct = $this->standProductServices->addStandProduct(
            $request->input('name'),
            $request->input('stand_id'),
            $request->input('description'),
            $request->input('main_img'),
        ); 
			$resources = $request->has('product_resource_paths') ? $request->input('product_resource_paths') : [];
			$product_files = $request->has('product_file_paths') ? $request->input('product_file_paths') : [];
			$this->standProductServices->saveResourceStandProduct($resources, $standproduct->stand_product_id);
			$this->standProductServices->saveProductFiles($product_files, $standproduct->stand_product_id);
            $this->standProductServices->addAllProductTags($request->input('tags'), $standproduct->stand_product_id);
            $this->standProductServices->addAllProductLinks($request->input('links'), $standproduct->stand_product_id);
			return response()->json('Stand product added', 200);
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
        );
        $this->standProductServices->deleteOldTags($standproduct->stand_product_id);
        $this->standProductServices->addAllProductTags($request->input('tags'), $standproduct->stand_product_id);
        $this->standProductServices->deteAllProductLinks($standproduct->stand_product_id);
        $this->standProductServices->addAllProductLinks($request->input('links'), $standproduct->stand_product_id);
        
     
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

    public function deleteStandproduct($congress_id, $stand_product_id)
    {
        if (!$standproduct = $this->standProductServices->getStandProductById($stand_product_id)) {
            return response()->json('no such product found', 404);
        }
        $standproduct->delete();
        return response()->json(['response' => 'product deleted'], 200);
    }

    public function getStandProductById($congressId, $standproduct_id)
    {
        $product = $this->standProductServices->getStandProductById($standproduct_id);
        return response()->json($product, 200);

    }

    public function addTag($congress_id, Request $request)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $this->standProductServices->addTag($request, $congress_id);
        $tags = $this->standProductServices->getTags($congress_id);
        return response()->json($tags);
    }

    public function getTags($congress_id)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $tags = $this->standProductServices->getTags($congress_id);
        return response()->json($tags);
    }
}
