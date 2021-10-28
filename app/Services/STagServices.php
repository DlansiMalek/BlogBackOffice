<?php

namespace App\Services;


use App\Models\STag;
use App\Models\StandTag;



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
        if (sizeof($stags) > 0) {
            foreach ($stags as $stag) {
                $this->addStandTag($stag, $stand_id);
            }
        }
    }

    public function addStandTag($stag_id, $stand_id)
    {
        $stand_tag = new StandTag();
        $stand_tag->stag_id = $stag_id;
        $stand_tag->stand_id = $stand_id;
        $stand_tag->save();
    }

    public function deleteOldSTags($stand_id)
    {
        return StandTag::where('stand_id', '=', $stand_id)->delete();
    }
}
