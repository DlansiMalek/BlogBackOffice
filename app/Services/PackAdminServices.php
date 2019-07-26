<?php

namespace App\Services;

use App\Models\PackAdmin;
use App\Models\PackModule;
use Illuminate\Http\Request;

class PackAdminServices
{
    public function getAllPackByModule($moduleId)
    {
        return PackAdmin::where('module_id', '=', $moduleId)
            ->get();
    }

    public function getPackById($packId)
    {
    return PackAdmin::where('pack_id', '=', $packId)
        ->first();
    }


    public function getAllPacks (){ return PackAdmin::all();}

    public function updatePack(Request $request, $updatePack) {
    if (!$updatePack) {
        return null;
    }
    $updatePack->name = $request->input('name');
    $updatePack->type = $request->input('type');
    $updatePack->capacity = $request->input('capacity');
    $updatePack->price = $request->input('price');
    if ($request->input('type') == "Demo")
        $updatePack->price = 0;
    if ($request->input('type') == "Event")
        $updatePack->nbr_events = $request->input('nbr_events');
    if ($request->input('type') ==  "Durée")
        $updatePack->nbr_days = $request->input('nbr_days');
    $updatePack->update();
    return $updatePack;
}

    public function addModuleToPack($request ,$pack){
    if (!$pack) {
        return null;
    }
    $pm = new PackModule();
    $pm->pack_id = $pack->pack_id;
    $pm->module_id = $request->input('module_id');
    $pm->save();
}
    public function AddPack($request ,$pack){
        $pack->name = $request->input('name');
        $pack->type = $request->input('type');
        $pack->capacity = $request->input('capacity');
        $pack->price = $request->input('price');
        if ($request->input('type') == "Demo")
            $pack->price = 0;
        if ($request->input('type') == "Event")
            $pack->nbr_events = $request->input('nbr_events');
        if ($request->input('type') ==  "Durée")
            $pack->nbr_days = $request->input('nbr_days');
        $pack->save();
        return $pack;
    }
}