<?php

namespace App\Services;

use App\Models\Stand;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;


class StandServices
{


    public function addStand($name,$organization_id,$congress_id,$url_streaming) {
        $stand = new Stand();
        $stand->name = $name;
        $stand->organization_id = $organization_id;
        $stand->congress_id = $congress_id;
        $stand->url_streaming = $url_streaming;
        $stand->save();
        return $stand;
    }

    public function getStands($congress_id)
    {
        return Stand::where('congress_id', '=', $congress_id)->get();
        
    }


    public function getStandById($stand_id) {
        return Stand::where('stand_id','=', $stand_id)->first();

    }

    public function editStand($oldStand,$name,$congress_id,$organization_id) {
       
        $oldStand->name= $name;
        $oldStand->congress_id = $congress_id;
        $oldStand->organization_id = $organization_id;
        $oldStand->update();
        return $oldStand ;
    }



   
}