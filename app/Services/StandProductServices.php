<?php

namespace App\Services;

use App\Models\Stand;
use App\Models\StandProduct;
use App\Models\ResourceStand;
use App\Models\ResourceProduct;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Illuminate\Support\Facades\Log;

class StandProductServices
{


    public function addStandProduct($name, $stand_id, $description, $main_img, $brochure_file)
    {
        $standproduct = new StandProduct();
        $standproduct->name = $name;
        $standproduct->stand_id = $stand_id;
        $standproduct->description = $description;
        $standproduct->main_img = $main_img;
        $standproduct->brochure_file = $brochure_file;
        $standproduct->save();
        return $standproduct;
    }

    public function editStandProduct($oldStandProduct, $name, $description, $main_img, $brochure_file)
    {

        $oldStandProduct->name = $name;
        $oldStandProduct->description = $description;
        $oldStandProduct->main_img = $main_img;
        $oldStandProduct->brochure_file = $brochure_file;
        $oldStandProduct->update();
        return $oldStandProduct;
    }

    public function getStandProductById($standproduct_id)
    {
        return StandProduct::find($standproduct_id);
    }

    public function getStandproducts($stand_id)
    {
        return StandProduct::where('stand_id', '=', $stand_id)->with('docs')->get();
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
                    $this->addResourceStandProduct($resource['resource_id'], $stand_product_id, $resource['pivot']['file_name']);
                }
            }
        } else {
            foreach ($resources as $resource) {

                $this->addResourceStandProduct($resources['resource_id'], $stand_product_id, $resources['file_name']);
            }
        }
    }

    public function addResourceStandProduct($resource_id, $stand_product_id, $file_name)
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
}
