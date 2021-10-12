<?php

namespace App\Services;

use App\Models\GSTag;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GSTagServices
{
    public function getGSTags($congress_id)
    {
        return GSTag::where('congress_id', '=', $congress_id)->get();
    }

    public function addGSTag($gstag, $congress_id)
    {
       
      
            if (!$gstag){
                $gstag = new GSTag();
            }
       
        $gstag->label = $gstags->input('label');
        $gstag->congress_id = $congress_id;
        $gstag->save();
    
}
    
  
   

}
