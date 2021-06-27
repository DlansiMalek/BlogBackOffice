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
use Illuminate\Support\Facades\Log;

class TagServices
{
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

}
