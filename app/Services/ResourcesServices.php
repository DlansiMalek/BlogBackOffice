<?php

namespace App\Services;

use App\Models\Access;
use App\Models\Resource;

class ResourcesServices
{
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
}