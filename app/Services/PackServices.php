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
use App\Models\UserPack;
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
       
       $this->addItemAccesPack($pack_id,$acessIds);
    }

        private function addItemAccesPack($packId, $acessIds) {

            foreach($acessIds as $access_id) {
            $access_pack = new AccessPack();
            $access_pack->access_id = $access_id;
            $access_pack->pack_id = $packId;
            $access_pack->save();
            }
        }

    public function getPackIdsByPacks($packs) {
            $res = array();
            foreach ($packs as $pack) {
                array_push($res, $pack->pack_id);
            }
            return $res;
        }
    public function affectPacksToUser($user_id, $packIds, $startPoint) {
        for ($i = $startPoint ; $i<sizeof($packIds) ; $i++) {
            $this->affectPackToUser($user_id,$packIds[$i]);
        }
    } 
    private function affectPackToUser($user_id,$packId) {
        $user_pack = new UserPack();
        $user_pack->user_id = $user_id;
        $user_pack->pack_id = $packId;
        $user_pack->save();
    }


    public function editUserPacksWithPackId($user_id, $user_packs, $packIds) {
        $loopLength = sizeof($user_packs) > sizeof($packIds) ? sizeof($packIds) : sizeof($user_packs);
        for ($i = 0 ; $i < $loopLength ; $i ++ ) {
            $user_packs[$i]['pack_id'] = $packIds[$i];
            $user_packs[$i]->update();
        }
        if (sizeof($user_packs) > sizeof($packIds)) {
            for ($i = $loopLength ; $i<sizeof($user_packs) ; $i++) {
                 $user_packs[$i]->delete();
            }
        }
         if (sizeof($user_packs) < sizeof($packIds)) {
                $this->affectPacksToUser($user_id,$packIds,$loopLength);
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