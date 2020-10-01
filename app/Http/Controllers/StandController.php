<?php

namespace App\Http\Controllers;

use App\Services\StandServices;
use Illuminate\Http\Request;



class StandController extends Controller
{
    protected $standServices;

    function __construct( StandServices $standServices )
        { $this->standServices = $standServices; }



    function addStand (Request $request) {
     
        $this->standServices->addStand(
           $request->input('name'),
           $request->input('organization_id'),
           $request->input('congress_id')
        );
        return response()->json('Stand added',200);
    }

    public function getStands($congress_id)
    {
        return response()->json($this->standServices->getStands($congress_id));
    }

    public function getStandById ($stand_id)
    {   return $this->standServices->getStandById($stand_id);
        
    }

    function editStand (Request $request, $stand_id) {
        $oldStand = $this->standServices->getStandById($stand_id);
     
      $this->standServices->editStand(
         $oldStand,
         $request->input('name'),
         $request->input('congress_id'),
         $request->input('organization_id')
      );
      return response()->json('stand updated',200);
     }

     public function deleteStand($stand_id)
     { $stand = $this->standServices->getStandById($stand_id);
       $stand->delete();
        return response()->json(['response' => 'stand deleted'],200);
     }

  





    
}
