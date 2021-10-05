<?php

namespace App\Services;

use App\Models\StandTag;
use App\Models\Stand;
use App\Models\STag;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class STagServices
{
    public function getSTags($congress_id)
    {
        return STag::where('congress_id', '=', $congress_id)->get();
    }

    public function addSTag($request, $congress_id)
    {
        $stag = new STag();
        $stag->label = $request->input('label');
        $stag->congress_id = $congress_id;
        $stag->save();
    }
    
    public function addAllStandTags($stags, $stand_id)
    {
        if (sizeof($stags) > 0)
        {
            foreach ($stags as $stag) {
                $this->addStandTag($tag, $stand_id);
            }
        }
    }

    public function addStandTag($stag_id, $stand_id)
    {
        $stand_tag = new StandTag();
        $stand_tag->stag_id = $stag_id;
        $stand_tag->stand_tag_id = $stand_id;
        $stand_tag->save();
    }

   

}
