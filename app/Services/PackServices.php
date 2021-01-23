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
        return Pack::where('pack_id', '=', $packId)->with(['users', 'accesses'])
            ->first();
    }

    public function addPack($congressId, $label, $description, $price, $accessIds)
    {

        $pack = new Pack();
        $pack->label = $label;
        $pack->description = $description;
        $pack->price = $price;
        $pack->congress_id = $congressId;
        $pack->save();
        $this->addAccessPack($pack->pack_id, $accessIds);

        return $pack;
    }

    private function addAccessPack($pack_id, $accessIds)
    {

        $this->addItemAccesPack($pack_id, $accessIds);
    }

    private function addItemAccesPack($packId, $accessIds)
    {
        foreach ($accessIds as $access_id) {
            $access_pack = new AccessPack();
            $access_pack->access_id = $access_id;
            $access_pack->pack_id = $packId;
            $access_pack->save();
        }
    }

    public function getPackIdsByPacks($packs)
    {
        $res = array();
        foreach ($packs as $pack) {
            array_push($res, $pack->pack_id);
        }
        return $res;
    }

    public function affectPacksToUser($user_id, $packIds, $startPoint)
    {
        for ($i = $startPoint; $i < sizeof($packIds); $i++) {
            $this->affectPackToUser($user_id, $packIds[$i]);
        }
    }

    private function affectPackToUser($user_id, $packId)
    {
        $user_pack = new UserPack();
        $user_pack->user_id = $user_id;
        $user_pack->pack_id = $packId;
        $user_pack->save();
    }


    public function editUserPacksWithPackId($user_id, $user_packs, $packIds)
    {
        $loopLength = sizeof($user_packs) > sizeof($packIds) ? sizeof($packIds) : sizeof($user_packs);
        for ($i = 0; $i < $loopLength; $i++) {
            $user_packs[$i]['pack_id'] = $packIds[$i];
            $user_packs[$i]->update();
        }
        if (sizeof($user_packs) > sizeof($packIds)) {
            for ($i = $loopLength; $i < sizeof($user_packs); $i++) {
                $user_packs[$i]->delete();
            }
        }
        if (sizeof($user_packs) < sizeof($packIds)) {
            $this->affectPacksToUser($user_id, $packIds, $loopLength);
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

    public function checkIfHasRelation($packId)
    {
        if ($user_pack = UserPack::where('pack_id', '=', $packId)->first())
            return 1;
        return 0;
    }

    public function getUserPacksByPackId($pack_id)
    {
        return UserPack::where('pack_id', '=', $pack_id)->get();
    }

    public function getAllAccessPackByPackId($pack_id)
    {
        return AccessPack::where('pack_id', '=', $pack_id)->get();
    }

    public function deleteAccessPack($access_id, $pack_id)
    {
        AccessPack::where('access_id', '=', $access_id)
            ->where('pack_id', '=', $pack_id)
            ->delete();
    }

    public function editpack($pack, $request)
    {

        if ($request->has('label')) $pack->label = $request->input("label");
        if ($request->has('description')) $pack->description = $request->input("description");
        $user_packs = $this->getUserPacksByPackId($pack->pack_id);
        if (count($user_packs) == 0) {
            if ($request->has('price')) {
                $pack->price = $request->input("price");
            }
            if ($request->has('accessIds')) {
                //delete all old access_packs
                $old_access_packs = $this->getAllAccessPackByPackId($pack->pack_id);

                foreach ($old_access_packs as $access_pack) {
                    $this->deleteAccessPack($access_pack->access_id, $pack->pack_id);
                }
                //add all new access_packs
                $this->addAccessPack($pack->pack_id, $request->accessIds);
            }
        }
        $pack->update();
        return $pack;
    }

}
