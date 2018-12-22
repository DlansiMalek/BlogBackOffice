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
        Access::where("congress_id",'=',$congress_id)->delete();
        $resAccesses = [];
        foreach ($accesss as $access) {
            $accessData = new Access();
            $accessData->name = $access["name"];
            $accessData->price = $access["price"];
            $accessData->packless = $access["packless"];
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
            ->where('access_parent', '=', null)
            ->where('intuitive', '=', null)
            ->get();
    }
}