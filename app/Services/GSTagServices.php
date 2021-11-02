<?php

namespace App\Services;

use App\Models\GSTag;

class GSTagServices
{
    public function getGSTags($congress_id)
    {
        return GSTag::where('congress_id', '=', $congress_id)->get();
    }

    public function addGSTag($request, $congress_id)
    {
        if (!$request->gstag_id) {
            $gstag = new GSTag();
        }
        $gstag->label = $request->input('label');
        $gstag->congress_id = $congress_id;
        $gstag->save();
    }
}
