<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Labo;

class LaboServices
{


    public function getAll()
    {
        return Labo::all();
    }

    public function getLabById($labId)
    {
        return Labo::find($labId);
    }
}