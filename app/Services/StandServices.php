<?php

namespace App\Services;

use App\Models\Stand;
use App\Models\ResourceStand;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;

class StandServices
{


    public function addStand($request)
    {

        $stand = new Stand();
        $stand->name = $request->input('name');
        $stand->organization_id = $request->input('organization_id');
        $stand->congress_id = $request->input('congress_id');
        $stand->booth_size = $request->input('booth_size');
        $stand->website_link = $request->input('website_link');
        $stand->fb_link = $request->input('fb_link');
        $stand->insta_link = $request->input('insta_link');
        $stand->twitter_link = $request->input('twitter_link');
        $stand->linkedin_link = $request->input('linkedin_link');
        $stand->priority = $request->input('priority');
        $stand->primary_color = $request->input('primary_color');
        $stand->secondary_color = $request->input('secondary_color');
        $stand->with_products = $request->input('with_products');
        $stand->save();
        return $stand;
    }

    public function saveResourceStand($resources, $stand_id)
    {
        // pas besoin du bloc de supression car une fois on supprime une ressource, le resourceStand correspondant est supprimé automatiquement
        $oldResources = ResourceStand::where('stand_id', '=', $stand_id)
            ->with(['resource'])
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if (($oldResource->file_name == $resource['pivot']['file_name']) && ($oldResource['resource_id'] !== $resource['resource_id'])) {
                        $this->editResourceStand($oldResource, $resource['resource_id']);
                        $isExist = true;
                        break;
                    }
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addResourceStand($resource['resource_id'], $stand_id, $resource['pivot']['file_name']);
                }
            }
        } else {
            foreach ($resources as $resource) {

                $this->addResourceStand($resource['resource_id'], $stand_id, $resource['pivot']['file_name']);
            }
        }
    }

    public function getAllStandByCongressId($congressId)
    {
        $stands =  Stand::where("congress_id", "=", $congressId)
            ->select('stand_id', 'name', 'status')
            ->get();
        return $stands;
    }



    public function addResourceStand($resourceId, $stand_id, $file_name)
    {
        $resourceStand = new ResourceStand();
        $resourceStand->resource_id = $resourceId;
        $resourceStand->stand_id = $stand_id;
        $resourceStand->file_name = $file_name;
        $resourceStand->save();

        return $resourceStand;
    }
    public function editResourceStand($resource, $resourceId)
    {
        $resource->resource_id = $resourceId;
        $resource->version = $resource->version + 1;
        $resource->update();
        return $resource;
    }

    public function getStandById($stand_id)
    {
        return Stand::where('stand_id', '=', $stand_id)
            ->with(['docs', 'organization','products'])
            ->first();
    }

    public function editStand($oldStand, $name, $organization_id, $congress_id, $url_streaming, $booth_size, $website_link, $fb_link, $insta_link, $twitter_link, $linkedin_link, $priority, $primary_color, $secondary_color)
    {

        $oldStand->name = $name;
        $oldStand->organization_id = $organization_id;
        $oldStand->url_streaming = $url_streaming;
        $oldStand->booth_size = $booth_size;
        $oldStand->website_link = $website_link;
        $oldStand->fb_link = $fb_link;
        $oldStand->insta_link = $insta_link;
        $oldStand->twitter_link = $twitter_link;
        $oldStand->linkedin_link = $linkedin_link;
        $oldStand->priority = $priority;
        $oldStand->primary_color = $primary_color;
        $oldStand->secondary_color = $secondary_color;
        $oldStand->update();
        return $oldStand;
    }
    public function getStands($congress_id, $name = null, $status = null)
    {
        return Stand::where(function ($query) use ($name, $status) {
            if ($name) {
                $query->where('name', '=', $name);
            }
            if ($status) {
                $query->where('status', '=', $status);
            }
        })
            ->with(['docs', 'products' , 'organization' => function ($query) {
                $query->with('resource');
            }])
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
                        "path" => UrlUtils::getFilesUrl() . $doc->path,
                        "filename" => $doc->pivot->file_name,
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

    public function modifyStatusStand($stand_id, $status)
    {
        return Stand::where('stand_id', '=', $stand_id)
            ->update(['status' => $status]);
    }
}
