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
            /*if (array_key_exists('ponderation', $access))
                $accessData->ponderation = $access["ponderation"];
            */
            if (array_key_exists('duration', $access))
                $accessData->duration = $access["duration"];

            $accessData->congress_id = $congress_id;
            $accessData->save();
            array_push($resAccesses, $accessData);
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
        return User::with(['grade'])->whereHas('accesss', function ($query) use ($accessId) {
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