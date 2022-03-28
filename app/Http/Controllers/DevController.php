<?php

namespace App\Http\Controllers;


use App\Services\DevServices;
use Illuminate\Http\Request;

class DevController extends Controller
{
    private $devServices;

    function __construct(DevServices $devServices)
    {

        $this->devServices = $devServices;
    }
    
    public function clearCache() {
        return $this->devServices->clearCache();
    }
}
