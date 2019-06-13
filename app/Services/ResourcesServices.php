<?php

namespace App\Services;

use App\Resource;
use Illuminate\Http\Request;

class ResourcesServices
{
    private $path = 'resources/';

    public function uploadResource($file)
    {
        $timestamp = microtime(true)*10000;
        $path = $file->storeAs( $this->path.$timestamp,$file->getClientOriginalName());

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
}