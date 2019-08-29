<?php

namespace App\Services;
use App\Models\PackAdmin;
use App\Models\HistoryPack;

class HistoryServices
{
    public  function getAllHistories(){
        return HistoryPack::all();
    }


}
