<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Access;
use App\Models\Admin_Access;

class AccessServices
{


    public function addAccessToCongress($congress_id, $accesss)
    {
        // $this->deleteAccessByCongress($congress_id);
        foreach ($accesss as $access) {
            $accessData = new Access();
            $accessData->name = $access["name"];
            $accessData->price = $access["price"];
            if (array_key_exists('ponderation', $access))
                $accessData->ponderation = $access["ponderation"];
            if (array_key_exists('duration', $access))
                $accessData->duration = $access["duration"];

            $accessData->congress_id = $congress_id;
            $accessData->save();

            $this->addResponsibles($accessData->access_id, $access["responsibleIds"]);
        }

    }

    public function getById($accessId)
    {
        return Access::find($accessId);
    }

    private function deleteAccessByCongress($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->delete();
    }

    public function addResponsibles($access_id, $responsibleIds)
    {
        foreach ($responsibleIds as $responsibleId) {
            $admin_access = new Admin_Access();
            $admin_access->admin_id = $responsibleId;
            $admin_access->access_id = $access_id;
            $admin_access->save();
        }
    }

    public function getAllAccessByCongress($congressId)
    {
        return Access::with(['participants'])
            ->where("congress_id", "=", $congressId)
            ->get();
    }
}