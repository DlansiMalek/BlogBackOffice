<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/04/2018
 * Time: 16:27
 */

namespace App\Http\Controllers;


use App\Services\SharedRepository;

class SharedController extends Controller
{
    protected $sharedRepository;

    function __construct(SharedRepository $sharedRepository)
    {
        $this->sharedRepository = $sharedRepository;
    }

    function getAllNiveaux()
    {
        return response()->json($this->sharedRepository->getAllNiveaux());

    }

    function getAllServices()
    {
        return response()->json($this->sharedRepository->getAllServices());

    }


}