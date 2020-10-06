<?php

namespace App\Services;

use App\Models\Access;
use App\Models\Resource;
use App\SpeakerAccess;

class ResourcesServices
{

    public function saveResource($path,$size)
    {
        $resource= new Resource();
        $resource->path=$path;
        $resource->size=$size;
        $resource->save();
        return $resource; 
    }

    public function getResourceByPath($path){
        return Resource::where('path','=',$path)->first();
    }

    private $path = 'resources/';

    public function uploadResource($file)
    {
        $timestamp = microtime(true) * 10000;
        $path = $file->storeAs($this->path . $timestamp, $file->getClientOriginalName());

        $resource = new Resource();
        $resource->path = $path;
        $resource->save();

        return $resource;
    }

    public function setAccessId($resource_id, $access_id)
    {
        if (!$resource = Resource::find($resource_id)) return null;
        $resource->access_id = $access_id;
        return $resource;
    }

    public function addResources(Access $access, $resources)
    {
        foreach ($resources as $resource) {
            $dbResource = Resource::find($resource);
            $dbResource->access_id = $access->access_id;
            $dbResource->update();
        }
    }

    public function removeAllResources($access_id)
    {
        $resources = Resource::where('access_id','=',$access_id)->get();
        foreach ($resources as $resource){
            $resource->access_id = null;
            $resource->update();
        }
    }

    public function editAccessResources($access_id, $newResources)
    {
        $oldResources = Resource::where('access_id','=',$access_id)->get();
        foreach ($oldResources as $old) {
            $found = false;
            foreach ($newResources as $new) {
                if ($new == $old->user_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $old->access_id = null;
                $old->save();
            }
        }
        foreach ($newResources as $new){
            $found = false;
            foreach ($oldResources as $old){
                if ($old->user_id == $new){
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $resource = Resource::find($new);
                $resource->access_id = $access_id;
                $resource->update();
            }
        }
    }

    public function getResourceByResourceId($resourceId){
        return Resource::where('resource_id','=',$resourceId)->first();
    }
}