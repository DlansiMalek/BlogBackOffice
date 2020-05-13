<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;

use App\Models\AccessPack;
use App\Models\Pack;

class PackServices
{


    public function getAllPackByCongress($congressId)
    {
        return Pack::where('congress_id', '=', $congressId)
            ->get();
    }

    public function getPackById($packId)
    {
        return Pack::where('pack_id', '=', $packId)
            ->first();
    }
    public function addPack($congressId, $label , $description, $price , $accessIds) {
        
        $pack = new Pack();
        $pack->label = $label;
        $pack->description = $description;
        $pack->price = $price ;
        $pack->congress_id = $congressId;
        $pack->save();
        $this->addAccessPack($pack->pack_id, $accessIds);
        
        return $pack;
    }

    private function addAccessPack($pack_id , $acessIds) {
       
        foreach($acessIds as $access_id) {
            $access_pack = new AccessPack();
            $access_pack->access_id = $access_id;
            $access_pack->pack_id = $pack_id;
            $access_pack->save();
        }


    }

    public function addPacks($accesses, $packs, $congress)
    {
        Pack::where('congress_id', "=", $congress->congress_id)->delete();
        foreach ($packs as $p) {
            $pack = new Pack();
            $pack->label = $p["label"];
            $pack->description = $p["description"];
            $pack->price = $p["price"];
            $pack->congress_id = $congress->congress_id;
            $pack->save();
            foreach ($p["accessIds"] as $access_front_id) {
                $ap = new AccessPack();
                $ap->access_id = $accesses[$access_front_id]->access_id;
                $ap->pack_id = $pack->pack_id;
                $ap->save();
            }
        }

    }

}