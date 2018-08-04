<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Lieu_Ex;


class SharedServices
{

    public function getAllGrades()
    {
        return Grade::all();
    }

    public function getAllLieux()
    {
        return Lieu_Ex::all();
    }
}