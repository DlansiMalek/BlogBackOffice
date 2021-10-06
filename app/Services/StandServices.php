<?php

namespace App\Services;

use App\Models\ResourceStand;
use App\Models\Stand;
use App\Models\StandContentConfig;
use App\Models\StandContentFile;
use App\Models\StandType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StandServices
{

    public function addStand($stand, $congressId, $request)
    {
        if (!$stand) {
            $stand = new Stand();
        }

        $stand->name = $request->input('name');
        $stand->organization_id = $request->input('organization_id');
        $stand->congress_id = $congressId;
        $stand->url_streaming = $request->input("url_streaming");
        $stand->booth_size = $request->input("booth_size");
        $stand->priority = $request->input("priority");
        $stand->primary_color = $request->input("primary_color");
        $stand->secondary_color = $request->input("secondary_color");
        $stand->floor_color = $request->input("floor_color");
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
        $stands = Stand::where("congress_id", "=", $congressId)
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
            ->with(['docs' => function ($query) {
                $query->select('Resource.*', 'Resource_Stand.file_name');
            },'products', 'organization.membres' => function ($query) {
                    $query->where('privilege_id', '=', config('privilege.Organisme'));
                }, 'organization.membres.profile_img', 'faq'])
            ->first();
    }

    public function getStandCachedById($stand_id)
    {
        $cacheKey = 'stand-' . $stand_id;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $stand = $this->getStandById($stand_id);
        Cache::put($cacheKey, $stand, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;

        return $stand;
    }

    public function getStands($congress_id,  $name = null, $status = null,$perPage = null,$search=null)
    {
        $allStand = Stand::where(function ($query) use ($name, $status,$search) {
            if ($name) {
                $query->where('name', '=', $name);
            }
            if ($search) {
                $query->where('name','LIKE', '%' . $search . '%');
            }
            if ($status) {
                $query->where('status', '=', $status);
            }
        })
            ->with(['docs', 'products', 'organization', 'faq'])
            ->orderBy(DB::raw('ISNULL(priority), priority'), 'ASC')
            ->where('congress_id', '=', $congress_id)
            ->orWhereHas("organization", function ($query) use ($search) {
                $query->where('name','LIKE', '%' . $search . '%');
                });

        return $allStand = $perPage ? $allStand->paginate($perPage) : $allStand->get();

    }

    public function getCachedStands($congress_id, $page, $perPage,$search)
    {
        $cacheKey = 'stands-' . $congress_id . $page . $perPage.$search;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $stands = $this->getStands($congress_id, null,null, $perPage,$search);
        Cache::put($cacheKey, $stands, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;

        return $stands;
    }

    public function getStandsPagination($congress_id, $perPage)
    {
        $response = Stand::with(['docs', 'organization', 'faq',
            'organization.admin' => function ($query) {
                $query->join('User','User.email', '=' ,'Admin.email')
                ->leftJoin('Resource','Resource.resource_id','User.resource_id')
                ->select('Admin.admin_id', 'User.user_id', 'User.gender', 'User.first_name', 'User.last_name', 'User.mobile','Resource.resource_id','Resource.path as img_user');
            }, 
            'stand_content_file', 'stand_type'])
            ->where('congress_id', '=', $congress_id)->paginate($perPage);
            $data = $response->getCollection()->transform(function($stand) {
                $stand->files = count($stand->stand_content_file) > 0 ? $this->setFilesWithContent($stand, $stand->stand_type_id) : $this->setFilesNoContent($stand->stand_type_id); 
                return $stand;
            });
            $res = array($response);
            $res['data'] = $data;
        return $res[0];
    }
    
    public function setFilesNoContent($stand_type_id)
    {
        $configs = $this->getStandContentConfigByTypeId($stand_type_id);
        $files = [];
        foreach ($configs as $config) {
            $data = [
                "key" => $config->key,
                "label" =>$config->label,
                "accept_file" => $config->accept_file,
                "file" => $config->default_file ? $config->default_file : $config->default_url
            ];
            array_push($files, $data);
        }
        return $files;
    }

    public function setFilesWithContent($stand, $stand_type_id)
    {
        $configs = $this->getStandContentConfigByTypeId($stand_type_id);
        $files = [];
        foreach ($configs as $config) {
            $file = $this->getStandContentFileByStandId($stand->stand_id, $config->stand_content_config_id);
            if ($file) {
                $data = [
                    "key" => $file->key,
                    "label" => $file->label,
                    "accept_file" => $file->accept_file,
                    "file" => $file->file ? $file->file : $file->url
                ];
            } else {
                $data = [
                    "key" => $config->key,
                    "label" => $config->label,
                    "accept_file" => $config->accept_file,
                    "file" => $config->default_file ? $config->default_file : $config->default_url
                ];
            }
            array_push($files, $data);
        }
        return $files;
    }

    public function getStandContentConfigByTypeId($stand_type_id)
    {
        return StandContentConfig::where('stand_type_id', '=', $stand_type_id)->get();
    }

    public function getStandContentFileByStandId($stand_id, $stand_content_config_id)
    {
        return StandContentFile::where('stand_id', '=', $stand_id)
        ->where('stand_content_config_id', '=', $stand_content_config_id)
        ->first();
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
                        "version" => $doc->pivot->version,
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
                    "url" => $stand->url_streaming,
                )
            );
        }

        foreach ($accesses as $access) {
            array_push(
                $res,
                array(
                    "channel_name" => $access->name,
                    "url" => $access->url_streaming,
                    "quizs" => $access->quizs,
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
    public function addStandFromExcel($stand, $name, $congressId, $organizationId)
    {
        if (!$stand) {
            $stand = new Stand();
        }
        $stand->name = $name;
        $stand->organization_id = $organizationId;
        $stand->congress_id = $congressId;
        $stand->save();
    }
    public function getStandByCongressIdOrgizantionIdAndName($name, $congressId, $organizationId)
    {
        return Stand::whereRaw('lower(name) like (?)', ["{$name}"])
            ->where('congress_id', '=', $congressId)
            ->where('organization_id', '=', $organizationId)->first();
    }

    public function getAllStandTypes()
    {
        return StandType::get();
    }

    public function getStandTypeById($stand_type_id)
    {
        return StandType::where('stand_type_id', '=', $stand_type_id)->first();
    }

    public function getContentConfigByStandType($stand_id, $stand_type_id)
    {
        return StandContentConfig::where('stand_type_id', '=', $stand_type_id)
            ->with(['stand_content_file' => function ($query) use ($stand_id) {
                $query->where('Stand_Content_File.stand_id', '=', $stand_id)
                    ->select('Stand_Content_File.*');
            }])->get();
    }

    public function editStandType($stand_type_id, $stand)
    {
        $stand->stand_type_id = $stand_type_id;
        $stand->update();
    }

    public function editStandContentFiles($data, $stand_id)
    {
        foreach ($data as $d) {
            $file = null;
            if (count($d['stand_content_file']) > 0) {
                $file = $this->getStandContentFile($d['stand_content_file'][0]['stand_content_file_id']);
                $this->editStandContentFile($file, $d, $stand_id);
            }
        }
    }

    public function editStandContentFile($file, $data, $stand_id)
    {
        $contentFile = $file != null ? $file : new StandContentFile();
        $contentFile->url = $data['stand_content_file'][0]['url'];
        $contentFile->file = $data['stand_content_file'][0]['file'];
        $contentFile->stand_id = $stand_id;
        $contentFile->stand_content_config_id = $data['stand_content_config_id'];
        $contentFile->save();
    }

    public function getStandContentFile($stand_content_file_id)
    {
        return StandContentFile::where('stand_content_file_id', '=', $stand_content_file_id)
            ->first();
    }

    public function getStandContentFiles($stand_content_file_id)
    {
        return StandContentFile::where('stand_content_file_id', '=', $stand_content_file_id)
            ->first();
    }

}
