<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Access;
use App\Models\Type_Access;

class AccessServices
{


    public function getAllTypesAccess()
    {
        return Type_Access::all();
    }

    public function addAccessToCongress($congress_id, $accesss)
    {
        foreach ($accesss as $access) {
            $accessData = new Access();
            $accessData->type_access_id = $access["type_access_id"];
            $accessData->price = $access["price"];
            $accessData->congress_id = $congress_id;
            $accessData->save();
        }

    }
}