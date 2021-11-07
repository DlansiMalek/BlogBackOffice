<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GSTagServices;
use App\Services\CongressServices;

class GSTagController extends Controller
{
    protected $gstagServices;
    protected $congressServices;

    function __construct(
        GSTagServices $gstagServices,
        CongressServices $congressServices
    ) {
        $this->gstagServices = $gstagServices;
        $this->congressServices = $congressServices;
    }

    public function addGSTag($congress_id, Request $request)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $this->gstagServices->addGSTag($request, $congress_id);
        $gstags = $this->gstagServices->getGSTags($congress_id);
        return response()->json($gstags);
    }

    public function getGSTags($congress_id)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $gstags = $this->gstagServices->getGSTags($congress_id);
        return response()->json($gstags);
    }

    public function getGTags($congress_id, $gstag_id)
    {
        if (!$this->congressServices->getCongressById($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $gstags = $this->gstagServices->getStagByGSTagId($congress_id, $gstag_id);
        return response()->json($gstags);
    }
}
