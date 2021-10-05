<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\STagServices;
use App\Services\CongressServices;

class STagController extends Controller
{
    protected $stagServices;
    protected $congressServices;

    function __construct(
        STagServices $stagServices,
        CongressServices $congressServices
    ) {
        $this->stagServices = $stagServices;
        $this->congressServices = $congressServices;
    }

    public function addSTag($congress_id, Request $request)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $this->stagServices->addSTag($request, $congress_id);
        $stags = $this->stagServices->getSTags($congress_id);
        return response()->json($stags);
    }

    public function getSTags($congress_id)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $stags = $this->stagServices->getSTags($congress_id);
        return response()->json($stags);
    }
}
