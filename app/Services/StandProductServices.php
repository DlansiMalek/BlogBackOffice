<?php

namespace App\Services;

use App\Models\ProductLink;
use App\Models\ProductFile;
use App\Models\ProductTag;
use App\Models\Stand;
use App\Models\StandProduct;
use App\Models\ResourceStand;
use App\Models\ResourceProduct;
use App\Models\Tag;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Illuminate\Support\Facades\Log;

class StandProductServices
{


    public function addStandProduct($name, $stand_id, $description, $main_img)
    {
        $standproduct = new StandProduct();
        $standproduct->name = $name;
        $standproduct->stand_id = $stand_id;
        $standproduct->description = $description;
        $standproduct->main_img = $main_img;
        $standproduct->save();
        return $standproduct;
    }

    public function editStandProduct($oldStandProduct, $name, $description, $main_img)
    {

        $oldStandProduct->name = $name;
        $oldStandProduct->description = $description;
        $oldStandProduct->main_img = $main_img;
        $oldStandProduct->update();
        return $oldStandProduct;
    }

    public function getStandProductById($standproduct_id)
    {
        return StandProduct::where('stand_product_id', '=', $standproduct_id)
        ->with(['docs', 'files'])
        ->first();
    }

    public function getStandproducts($stand_id)
    {
        return StandProduct::where('stand_id', '=', $stand_id)->with(['docs'])
            ->get();
    }

    public function saveResourceStandProduct($resources, $stand_product_id)
    {
   
        $oldResources = ResourceProduct::where('stand_product_id', '=', $stand_product_id)
            ->with(['resource'])
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] !== $resource) {
                        $this->editResourceStandProduct($oldResource, $resource);
                        $isExist = true;
                        break;
                    }
                    if ($oldResource['resource_id'] == $resource) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addResourceStandProduct($resource, $stand_product_id);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addResourceStandProduct($resource, $stand_product_id);
            }
        }
    }

    public function addResourceStandProduct($resource_id, $stand_product_id)
    {
        $resourceStand = new ResourceProduct();
        $resourceStand->resource_id = $resource_id;
        $resourceStand->stand_product_id = $stand_product_id;
        $resourceStand->save();
        return $resourceStand;
    }

    public function editResourceStandProduct($resource, $resourceId)
    {
        $resource->resource_id = $resourceId;
        $resource->update();
        return $resource;
    }

    public function getTags($congress_id)
    {
        return Tag::where('congress_id', '=', $congress_id)->get();
    }

    public function addTag($request, $congress_id)
    {
        $tag = new Tag();
        $tag->label = $request->input('label');
        $tag->congress_id = $congress_id;
        $tag->save();
    }

    public function saveProductFiles($resources, $stand_product_id)
    {
   
        $oldResources = ProductFile::where('stand_product_id', '=', $stand_product_id)
            ->with(['resource'])
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] !== $resource) {
                        $this->editProductFile($oldResource, $resource);
                        $isExist = true;
                        break;
                    }
                    if ($oldResource['resource_id'] == $resource) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addProductFile($resource, $stand_product_id);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addProductFile($resource, $stand_product_id);
            }
        }
    }

    public function addProductFile($resource_id, $stand_product_id)
    {
        $productFile = new ProductFile();
        $productFile->resource_id = $resource_id;
        $productFile->stand_product_id = $stand_product_id;
        $productFile->save();
        return $productFile;
    }

    public function editProductFile($productFile, $resourceId)
    {
        $productFile->resource_id = $resourceId;
        $productFile->update();
        return $productFile;
    }

    public function addAllProductTags($tags, $stand_product_id)
    {
        foreach ($tags as $tag)
        {
            $this->addProductTag($tag, $stand_product_id);
        }
    }

    public function addProductTag($tag_id, $stand_product_id)
    {
        $product_tag = new ProductTag();
        $product_tag->tag_id = $tag_id;
        $product_tag->stand_product_id = $stand_product_id;
        $product_tag->save();
    }

    public function deleteOldTags($stand_product_id)
    {
        return ProductTag::where('stand_product_id', '=', $stand_product_id)->delete();
    }

    public function addAllProductLinks($links, $stand_product_id)
    {
        foreach ($links as $link)
        {
            $this->addProductLink($link['link'], $stand_product_id);
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
}
