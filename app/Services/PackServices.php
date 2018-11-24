<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Access;
use App\Models\Access_Pack;
use App\Models\Pack;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PackServices
{


    public function getAllPackByCongress($congressId)
    {
        return Pack::where('congress_id', '=', $congressId)
            ->get();
    }

    public function addPacks($accesses,$packs, $congress){
        $accesses_map = [];
        for ($accesses as $access){
            //TODO
        }
        foreach ($packs as $p)
        $pack = new Pack();
        $pack->label = $p["label"];
        $pack->description = $p["description"];
        $pack->price = $p["price"];
        $pack->congress_id = $congress->congress_id;
        $pack->save();
        foreach ($packs["accesses"] as $access){
            $ap = new Access_Pack();
            $ap->access_id =
        }
    }
}