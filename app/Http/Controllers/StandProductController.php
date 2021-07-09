<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StandServices;
use App\Services\StandProductServices;
use App\Services\CongressServices;
use App\Services\TagServices;

class StandProductController extends Controller
{
    protected $standServices;
    protected $standProductServices;
    protected $congressServices;
    protected $tagServices;

    function __construct(StandServices $standServices, 
    StandProductServices $standProductServices, 
    CongressServices $congressServices,
    TagServices $tagServices)
    {
        $this->standServices = $standServices;
        $this->standProductServices = $standProductServices;
        $this->congressServices = $congressServices;
        $this->tagServices = $tagServices;
    }

    public function editStandProduct($congress_id, $stand_id, $standproduct_id,  Request $request)
    {

        if (!$stand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        $oldStandProduct = null;
        if ($standproduct_id != null)
        $oldStandProduct = $this->standProductServices->getStandProductById($standproduct_id);

        $standproduct = $this->standProductServices->editStandProduct(
            $oldStandProduct,
            $request->input('name'),
            $request->input('description'),
            $request->input('main_img'),
            $request->input('stand_id')
        );
        $this->standProductServices->saveResourceStandProduct($request->input('imgs'), $standproduct->stand_product_id);
        $this->standProductServices->saveProductFiles($request->input('files'), $standproduct->stand_product_id);
        $this->standProductServices->saveProductVideos($request->input('videos'), $standproduct->stand_product_id);
        $this->tagServices->deleteOldTags($standproduct->stand_product_id);
        $this->tagServices->addAllProductTags($request->input('tags'), $standproduct->stand_product_id);
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

    public function getProductsBy3DBooth($congressId, $boothId) {
        if (!$this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        if (!$this->standServices->getStandById($boothId)) {
            return response()->json(['response' => 'Booth not found', 404]);
        }

        $standproducts = $this->standProductServices->getStandproducts($boothId);
        return response()->json($standproducts, 200);
    }
}
