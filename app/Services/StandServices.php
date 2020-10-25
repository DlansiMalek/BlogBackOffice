<?php

namespace App\Services;

use App\Models\Stand;
use App\Models\ResourceStand;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;

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

    public function saveResourceStand($resources,$stand_id) {
        $oldResources = ResourceStand::where('stand_id', '=', $stand_id)
        ->with(['resource'])
        ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false ;
                $resource['path'] = str_replace('('.$resource['resource_id'].')','',$resource['path']);
                foreach ($oldResources as $oldResource) {
                    $oldResource['resource']['path'] = str_replace('('.$oldResource['resource_id'].')','',$oldResource['resource']['path']);
                    if ( ($oldResource['resource']['path'] == $resource['path']) && ($oldResource['resource_id'] !== $resource['resource_id'])) {
                     $this->editResourceStand($oldResource,$resource['resource_id']);
                     $isExist = true ;
                     break ;
                    }
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true ;
                    break;
                    }
                } if (!$isExist ) {
                    $this->addResourceStand($resource['resource_id'],$stand_id);
                }
                
            }
        } else {
            foreach ($resources as $resource) {

                $this->addResourceStand($resource['resource_id'], $stand_id);
            }
        }
        
    }

    public function getAllStandByCongressId($congressId)
    {
        $stands =  Stand::where("congress_id", "=", $congressId)
        ->select('stand_id','name')
            ->get();
         return $stands;
        }

        

    public function addResourceStand($resourceId, $stand_id)
    {
        $resourceStand = new ResourceStand();
        $resourceStand->resource_id = $resourceId;
        $resourceStand->stand_id = $stand_id;
        $resourceStand->save();

        return $resourceStand;

    }
    public function editResourceStand($resource,$resourceId)
    {
        $resource->resource_id = $resourceId;
        $resource->version = $resource->version + 1  ;
        $resource->update();
        return $resource;

    }

    public function getStandById($stand_id) {
        return Stand::where('stand_id','=', $stand_id)
        ->with(['docs','organization'])
        ->first();

    }

    public function editStand($oldStand,$name,$congress_id,$organization_id,$url_streaming) {
       
        $oldStand->name= $name;
        $oldStand->congress_id = $congress_id;
        $oldStand->organization_id = $organization_id;
        $oldStand->url_streaming = $url_streaming;
        $oldStand->update();
        return $oldStand ;
    }
    public function getStands($congress_id, $name = null)
    {
        return Stand::where(function ($query) use ($name) {
            if ($name) {
                $query->where('name', '=', $name);
            }
        })
            ->with(['docs','organization'])
            ->where('congress_id', '=', $congress_id)->get();
    }

    

    public function getDocsByStands($stands)
    {
        $res = array();

        foreach ($stands as $stand) {
            foreach ($stand->docs as $doc) {
                array_push(
                    $res,
                    array(
                        "stand" => $stand->name,
                        "path" => UrlUtils::getBaseUrl() . '/resource/' . $doc->path,
                        "filename" => $doc->path,
                        "version" => $doc->pivot->version
                    )
                );
            }
        }
        return $res;
    }

    public function getUrlsByStandsAndAccess($stands, $accesses)
    {
        $res = array();

        foreach ($stands as $stand) {
            array_push(
                $res,
                array(
                    "channel_name" => $stand->name,
                    "url" => $stand->url_streaming
                )
            );
        }

        foreach ($accesses as $access) {
            array_push(
                $res,
                array(
                    "channel_name" => $access->name,
                    "url" => $access->url_streaming,
                    "quizs" => $access->quizs
                )
            );
        }
        return $res;
    }

    public function modifyAllStatusStand($congressId, $status)
    {
        return Stand::where('congress_id', '=', $congressId)
            ->update(['status' => $status]);
    }

    public function getStatusGlobalStand($stands)
    {
        foreach ($stands as $stand) {
            if ($stand->status == 1) {
                return true;
            }
        }
        return false;
    }
}
