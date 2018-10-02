<?php

namespace App\Http\Controllers;


use App\Services\LaboServices;

class LaboController extends Controller
{

    protected $laboServices;


    function __construct(LaboServices $laboServices)
    {
        $this->laboServices = $laboServices;
    }


    public function getAll()
    {
        return response()->json($this->laboServices->getAll());
    }


}
