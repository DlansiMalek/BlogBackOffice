<?php

namespace App\Services;

use App\Models\GSTag;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class GSTagServices
{
    public function getGSTags($congress_id)
    {
        return GSTag::where('congress_id', '=', $congress_id)->get();
    }

    public function addGSTag($request, $congress_id)
    {
      
           if (!$request->gstag_id){
               $gstag = new GSTag();
            }
        $gstag->label = $request->input('label');
        $gstag->congress_id = $congress_id;
        $gstag->save();
    
}
    
  
   

}
