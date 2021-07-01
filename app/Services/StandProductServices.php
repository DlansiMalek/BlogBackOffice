<?php

namespace App\Services;

use App\Models\ProductLink;
use App\Models\ProductFile;
use App\Models\ProductVideo;
use App\Models\StandProduct;
use App\Models\ResourceProduct;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Illuminate\Support\Facades\Log;

class StandProductServices
{
    public function editStandProduct($oldStandProduct, $name, $description, $main_img, $stand_id)
    {
        if (!$oldStandProduct)
        $oldStandProduct = new StandProduct();
        $oldStandProduct->name = $name;
        $oldStandProduct->description = $description;
        $oldStandProduct->main_img = $main_img;
        $oldStandProduct->stand_id = $stand_id;
        $oldStandProduct->save();
        return $oldStandProduct;
    }

    public function getStandProductById($standproduct_id)
    {
        return StandProduct::where('stand_product_id', '=', $standproduct_id)
            ->with([
                'imgs' => function ($query) {
                    $query->select('Resource.*', 'Resource_Product.file_name');
                },
                'files'  => function ($query) {
                    $query->select('Resource.*', 'Product_File.file_name');
                },
                'videos' => function ($query) {
                    $query->select('Resource.*', 'Product_Video.file_name');
                }, 'product_tags', 'links'
            ])
            ->first();
    }

    public function getStandproducts($stand_id)
    {
        return StandProduct::where('stand_id', '=', $stand_id)->with(['product_tags','imgs','files', 'videos', 'links'])
            ->get();
    }

    public function saveResourceStandProduct($resources, $stand_product_id)
    {
        $oldResources = ResourceProduct::where('stand_product_id', '=', $stand_product_id)
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if (($oldResource->file_name == $resource['file_name']) && ($oldResource['resource_id'] !== $resource['resource_id'])) {
                        $this->editResourceStandProduct($oldResource, $resource['resource_id']);
                        $isExist = true;
                        break;
                    }
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addResourceStandProduct($resource['resource_id'], $stand_product_id, $resource['file_name']);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addResourceStandProduct($resource['resource_id'], $stand_product_id, $resource['file_name']);
            }
        }
    }

    public function addResourceStandProduct($resource_id, $stand_product_id, $fileName)
    {
        $resourceStand = new ResourceProduct();
        $resourceStand->resource_id = $resource_id;
        $resourceStand->stand_product_id = $stand_product_id;
        $resourceStand->file_name = $fileName;
        $resourceStand->save();
        return $resourceStand;
    }
    
    public function editResourceStandProduct($resource, $resourceId)
    {
        $resource->resource_id = $resourceId;
        $resource->update();
        return $resource;
    }

    public function saveProductFiles($resources, $stand_product_id)
    {
        $oldResources = ProductFile::where('stand_product_id', '=', $stand_product_id)
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if (($oldResource->file_name == $resource['file_name']) && ($oldResource['resource_id'] !== $resource['resource_id'])) {
                        $this->editProductFile($oldResource, $resource['resource_id']);
                        $isExist = true;
                        break;
                    }
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addProductFile($resource['resource_id'], $stand_product_id, $resource['file_name']);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addProductFile($resource['resource_id'], $stand_product_id, $resource['file_name']);
            }
        }
    }

    public function addProductFile($resource_id, $stand_product_id, $fileName)
    {
        $productFile = new ProductFile();
        $productFile->resource_id = $resource_id;
        $productFile->stand_product_id = $stand_product_id;
        $productFile->file_name = $fileName;
        $productFile->save();
        return $productFile;
    }
    
    public function editProductFile($resource, $resourceId)
    {
        $resource->resource_id = $resourceId;
        $resource->update();
        return $resource;
    }

    public function addAllProductLinks($links, $stand_product_id)
    {
        if (sizeof($links) > 0) {
            foreach ($links as $link) {
                if(!empty($link))
                $this->addProductLink($link['link'], $stand_product_id);
            }
        }
    }

    public function addProductLink($link_name, $stand_product_id)
    {
        $link = new ProductLink();
        $link->link = $link_name;
        $link->stand_product_id = $stand_product_id;
        $link->save();
    }

    public function deteAllProductLinks($stand_product_id)
    {
        return ProductLink::where('stand_product_id', '=', $stand_product_id)->delete();
    }

    public function saveProductVideos($resources, $stand_product_id)
    {
        $oldResources = ProductVideo::where('stand_product_id', '=', $stand_product_id)
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if (($oldResource->file_name == $resource['file_name']) && ($oldResource['resource_id'] !== $resource['resource_id'])) {
                        $this->editProductVideo($oldResource, $resource['resource_id']);
                        $isExist = true;
                        break;
                    }
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addProductVideo($resource['resource_id'], $stand_product_id, $resource['file_name']);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addProductVideo($resource['resource_id'], $stand_product_id, $resource['file_name']);
            }
        }
    }

    public function addProductVideo($resource_id, $stand_product_id, $fileName)
    {
        $productFile = new ProductVideo();
        $productFile->resource_id = $resource_id;
        $productFile->stand_product_id = $stand_product_id;
        $productFile->file_name = $fileName;
        $productFile->save();
        return $productFile;
    }

    public function editProductVideo($resource, $resourceId)
    {
        $resource->resource_id = $resourceId;
        $resource->update();
        return $resource;
    }
}
