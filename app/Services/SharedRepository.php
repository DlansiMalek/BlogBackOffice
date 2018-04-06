<?php
/**
 * Created by IntelliJ IDEA.
 * User: S4M37
 * Date: 19/12/2017
 * Time: 11:14
 */

namespace App\Services;


use App\Models\Niveau;
use App\Models\Service;

class SharedRepository
{

    public function getAllNiveaux()
    {
        return Niveau::all();
    }

    public function getAllServices()
    {
        return Service::all();
    }
}