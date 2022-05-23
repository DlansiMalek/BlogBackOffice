<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TagServices;
use App\Services\CongressServices;

class TagController extends Controller
{
    protected $tagServices;
    protected $congressServices;

    function __construct(
        TagServices $tagServices,
        CongressServices $congressServices
    ) {
        $this->tagServices = $tagServices;
        $this->congressServices = $congressServices;
    }

    public function addTag($congress_id, Request $request)
    {
        if (!$this->congressServices->isExistCongress($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $this->tagServices->addTag($request, $congress_id);
        $tags = $this->tagServices->getTags($congress_id);
        return response()->json($tags);
    }

    public function getTags($congress_id)
    {
        if (!$this->congressServices->isExistCongress($congress_id)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }
        $tags = $this->tagServices->getTags($congress_id);
        return response()->json($tags);
    }
}
