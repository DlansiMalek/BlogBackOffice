<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Access_Pack;
use App\Models\Pack;

class PackServices
{


    public function getAllPackByCongress($congressId)
    {
        return Pack::where('congress_id', '=', $congressId)
            ->get();
    }
    public function addPacks($accesses,$packs, $congress){
        Pack::where('congress_id',"=",$congress->congress_id)->delete();
        foreach ($packs as $p){
            $pack = new Pack();
            $pack->label = $p["label"];
            $pack->description = $p["description"];
            $pack->price = $p["price"];
            $pack->congress_id = $congress->congress_id;
            $pack->save();
            foreach ($p["accessIds"] as $access_front_id){
                $ap = new Access_Pack();
                $ap->access_id = $accesses[$access_front_id]->access_id;
                $ap->pack_id = $pack->pack_id;
                $ap->save();
            }
        }

    }

}