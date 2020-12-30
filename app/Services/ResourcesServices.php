<?php

namespace App\Services;

use App\Models\Access;
use App\Models\Resource;
use App\Models\ResourceSubmission;
use App\SpeakerAccess;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;

class ResourcesServices
{

    public function saveResource($path, $size)
    {
        $resource = new Resource();
        $resource->path = $path;
        $resource->size = $size;
        $resource->save();
        return $resource;
    }

    public function getResourceByPath($path)
    {
        return Resource::where('path', '=', $path)->first();
    }

    private $path = 'resources/';

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
        $resources = Resource::where('access_id', '=', $access_id)->get();
        foreach ($resources as $resource) {
            $resource->access_id = null;
            $resource->update();
        }
    }

    public function editAccessResources($access_id, $newResources)
    {
        $oldResources = Resource::where('access_id', '=', $access_id)->get();
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
        foreach ($newResources as $new) {
            $found = false;
            foreach ($oldResources as $old) {
                if ($old->user_id == $new) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $resource = Resource::find($new);
                $resource->access_id = $access_id;
                $resource->update();
            }
        }
    }

    function addResourceSubmission($resourceId, $submissionId)
    {

        $resourceSubmission = new ResourceSubmission();
        $resourceSubmission->resource_id = $resourceId;
        $resourceSubmission->submission_id = $submissionId;
        $resourceSubmission->save();

        return $resourceSubmission;
    }

    public function addRessourcesExternal($submission, $item)
    {
        $file = new Filesystem();
        $fileNames = explode(";", isset($item['files']) ? $item['files'] : '');

        foreach ($fileNames as $fileName) {
            if($fileName !== '') {
                $oldPath = storage_path('app/submissions') . '/' . $fileName;
                if ($file->exists($oldPath)) {
                    Log::info($oldPath);
                    $resource = $this->saveResource($fileName, 0);
                    $resource->path = '(' . $resource->resource_id . ')' . $resource->path;
                    $file->move($oldPath, storage_path('app/resource') . '/' . $resource->path);
                    $resource->update();
                    $this->addResourceSubmission($resource->resource_id, $submission->submission_id);
                }
            }
        }

    }
}
