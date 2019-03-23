<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Access;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AccessServices
{


    public function addAccessToCongress($congress_id, $accesss)
    {
        $resAccesses = [];
        foreach ($accesss as $access) {
            $accessData = new Access();
            $accessData->name = $access["name"];
            $accessData->price = $access["price"];
            $accessData->packless = $access["packless"];
            $accessData->intuitive = $access["intuitive"];
            $accessData->seuil = array_key_exists("seuil", $access) ? $access["seuil"] : null;
            $accessData->max_places = array_key_exists("max_places", $access) ? $access["max_places"] : null;
            $accessData->theoric_start_data = array_key_exists("theoric_start_data", $access) ? $access["theoric_start_data"] : null;
            $accessData->theoric_end_data = array_key_exists("theoric_end_data", $access) ? $access["theoric_end_data"] : null;
            /*if (array_key_exists('ponderation', $access))
                $accessData->ponderation = $access["ponderation"];
            */
            if (array_key_exists('duration', $access))
                $accessData->duration = $access["duration"];

            $accessData->congress_id = $congress_id;
            $accessData->save();
            $resAccesses[$access['front_id']] = $accessData;
        }
        return $resAccesses;

    }

    public function getById($accessId)
    {
        return Access::find($accessId);
    }

    public function getIntuitiveAccess($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->where('intuitive', '=', 1)
            ->get();
    }

    public function getIntuitiveAccessIds($congressId)
    {
        $accesss = $this->getIntuitiveAccess($congressId);
        Log::info($accesss);
        $res = array();
        foreach ($accesss as $access) {
            $accessId = $access->access_id;
            array_push($res, $accessId);
        }
        return $res;
    }

    public function getAccessIdsByAccess($accesss)
    {
        $res = array();
        foreach ($accesss as $access) {
            array_push($res, $access->access_id);
        }
        return $res;
    }

    public function getUserAccessByAccessId($accessId)
    {
        return User::whereHas('accesss', function ($query) use ($accessId) {
            $query->where('Access.access_id', '=', $accessId);
        })
            ->get();
    }

    public function getAllAccessByAccessIds($accessIds)
    {
        return $accessIds ? Access::whereIn('access_id', $accessIds)->get() : [];
    }


    private
    function deleteAccessByCongress($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->delete();
    }


    public
    function getAllAccessByCongress($congressId)
    {
        return Access::with(['participants'])
            ->where("congress_id", "=", $congressId)
            ->where('intuitive', '=', null)
            ->get();
    }

    function editAccessesList($oldAccesses, $newAccesses,$congressId)
    {

        foreach ($oldAccesses as $oldAccess) {
            $isDeleted = true;
            foreach ($newAccesses as $newAccess) {
                if (array_key_exists("access_id", $newAccess) && $newAccess['access_id'] == $oldAccess->access_id) {
                    $isDeleted = false;
                    break;
                }
            }
            if ($isDeleted) $oldAccess->delete();
        }
        foreach ($newAccesses as $newAccess) {
            if (!array_key_exists("access_id", $newAccess)) {
                $accessData = new Access();
                $accessData->name = $newAccess["name"];
                $accessData->price = $newAccess["price"];
                $accessData->packless = $newAccess["packless"];
                $accessData->intuitive = $newAccess["intuitive"];
                $accessData->seuil = array_key_exists("seuil", $newAccess) ? $newAccess["seuil"] : null;
                $accessData->max_places = array_key_exists("max_places", $newAccess) ? $newAccess["max_places"] : null;
                $accessData->theoric_start_data = array_key_exists("theoric_start_data", $newAccess) ? $newAccess["theoric_start_data"] : null;
                $accessData->theoric_end_data = array_key_exists("theoric_end_data", $newAccess) ? $newAccess["theoric_end_data"] : null;
                $accessData->duration = $newAccess["duration"];

                /*if (array_key_exists('ponderation', $access))
                    $accessData->ponderation = $access["ponderation"];
                */

                $accessData->congress_id = $congressId;
                $accessData->save();
            }
            else {
                $access = Access::find($newAccess['access_id']);
                $access->price = $newAccess['price'];
                $access->name = $newAccess['name'];
                $access->duration  = $newAccess['duration'];
                $access->seuil = $newAccess['seuil'];
                $access->packless = $newAccess['packless'];
                $access->max_places = $newAccess['max_places'];
                $access->theoric_start_data = $newAccess['theoric_start_data'];
                $access->theoric_end_data = $newAccess['theoric_end_data'];
                $access->update();
            }
        }
        return $this->getAccessesByCongressId(false, $congressId);
    }

    function getAccessesByCongressId($intuitive, $congressId){
        return $intuitive?
            Access::where("congress_id", '=', $congressId)
            ->whereNotNull('intuitive')
            ->get():
            Access::where("congress_id", '=', $congressId)
            ->whereNull('intuitive')
            ->get();

    }
}
