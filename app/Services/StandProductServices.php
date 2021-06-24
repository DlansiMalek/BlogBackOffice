<?php

namespace App\Services;

use App\Models\ProductLink;
use App\Models\ProductFile;
use App\Models\ProductTag;
use App\Models\ProductVideo;
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
            ->with(['docs', 'files', 'product_tags', 'links', 'videos'])
            ->first();
    }

    public function getStandproducts($stand_id)
    {
        return StandProduct::where('stand_id', '=', $stand_id)->with(['docs', 'files', 'videos'])
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
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addResourceStandProduct($resource['resource_id'], $stand_product_id);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addResourceStandProduct($resource['resource_id'], $stand_product_id);
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
            ->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resources as $resource) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addProductFile($resource['resource_id'], $stand_product_id);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addProductFile($resource['resource_id'], $stand_product_id);
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

    public function addAllProductTags($tags, $stand_product_id)
    {
        if (sizeof($tags) > 0)
        {
            foreach ($tags as $tag) {
                $this->addProductTag($tag, $stand_product_id);
            }
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
        if (sizeof($links) > 0) {
            foreach ($links as $link) {
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
                    if ($oldResource['resource_id'] == $resource['resource_id']) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addProductVideo($resource['resource_id'], $stand_product_id);
                }
            }
        } else {
            foreach ($resources as $resource) {
                $this->addProductVideo($resource['resource_id'], $stand_product_id);
            }
        }
    }

    public function addProductVideo($resource_id, $stand_product_id)
    {
        $productFile = new ProductVideo();
        $productFile->resource_id = $resource_id;
        $productFile->stand_product_id = $stand_product_id;
        $productFile->save();
        return $productFile;
    }
}
