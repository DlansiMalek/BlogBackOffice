<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StandServices;
use App\Services\StandProductServices;
use Illuminate\Support\Facades\Log;

class StandProductController extends Controller
{
	 protected $standServices;
	 protected $standproductServices;
	 
	   function __construct(StandServices $standServices, StandProductServices $standproductServices)
    {
        $this->standServices = $standServices;
        $this->standproductServices = $standproductServices;
    }

	function addStandProduct (Request $request) {
     
        $standproduct = $this->standproductServices->addStandProduct(
            $request->input('name'),
            $request->input('stand_id'),
            $request->input('description'),
            $request->input('main_img'),
            $request->input('brochure_file'),
         );
		 
		 //$resources = $request->input('docs');
		 //only for testing :  because there is no gui  and resource created 
		$docs = array("resource_id"=>1, "pivot"=>"37", "file_name"=>"43");
		   $resources = $docs ;
         $this->standproductServices->saveResourceStandProduct($resources,$standproduct->stand_product_id);
		 return response()->json('Standproduct added',200);
     }
	 
	  public function editStandProduct($congress_id,$stand_id,$standproduct_id,  Request $request)
    {
         
        if (!$Stand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
		 if (!$oldStandProduct = $this->standproductServices->getStandProductById($standproduct_id)) {
            return response()->json(['response' => 'product not found', 404]);
        }
       
        $standproduct = $this->standproductServices->editStandProduct(
            $oldStandProduct,
            $request->input('name'),
            $request->input('description'),
            $request->input('main_img'),
            $request->input('brochure_file')
         );
         return response()->json($standproduct, 200);
    }
	 
	  public function getStandproducts($congress_id ,$stand_id)
    {
          if (!$Stand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        $standproducts = $this->standproductServices->getStandproducts($stand_id);
        return response()->json($standproducts, 200);
    }
	
	public function deleteStandproduct($congress_id ,$stand_product_id)
      {  
          if (!$standproduct = $this->standproductServices->getStandProductById($stand_product_id)) {
          return response()->json('no such product found' ,404);
      }
		 $standproduct->delete();
         return response()->json(['response' => 'product deleted'],200);    
      }
	  
	   public function getStandProductById ($congressId,$standproduct_id)
     {   return $this->standproductServices->getStandProductById($standproduct_id);
         
     }
}
