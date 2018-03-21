<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Add_Info;
use App\Models\Type_Info;

class AddInfoServices
{


    public function getAllTypesInfo()
    {
        return Type_Info::all();
    }

    public function addInfoToCongress($congress_id, $addInfos)
    {
        foreach ($addInfos as $info) {
            $addInfo = new Add_Info();
            $addInfo->type_info_id = $info;
            $addInfo->congress_id = $congress_id;
            $addInfo->save();
        }
    }
}