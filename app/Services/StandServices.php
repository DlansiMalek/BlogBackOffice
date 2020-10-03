<?php

namespace App\Services;

use App\Models\Stand;
use App\Models\ResourceStand;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;


class StandServices
{


    public function addStand($name,$organization_id,$congress_id) {
        $stand = new Stand();
        $stand->name = $name;
        $stand->organization_id = $organization_id;
        $stand->congress_id = $congress_id;
        $stand->save();
        return $stand;
    }

    public function saveResourceStand($resources_ids,$stand_id) {
        $oldResources = ResourceStand::where('stand_id', '=', $stand_id)->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources_ids as $resourceId) {
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] != $resourceId) {
                        $this->addResourceStand($resourceId, $stand_id);
                    }
                }
            }
        } else {
            foreach ($resources_ids as $resourceId) {

                $this->addResourceStand($resourceId, $stand_id);
            }
        }
        
    }

    public function addResourceStand($resourceId, $stand_id)
    {
        $resourceStand = new ResourceStand();
        $resourceStand->resource_id = $resourceId;
        $resourceStand->stand_id = $stand_id;
        $resourceStand->save();

        return $resourceStand;

    }

    public function getStands($congress_id)
    {
        return Stand::where('congress_id', '=', $congress_id)->with(['resources'])->get();
        
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