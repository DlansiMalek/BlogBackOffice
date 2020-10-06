<?php


namespace App\Http\Controllers;


use App\Services\ResourcesServices;
use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    protected $resourcesServices;

    function __construct(ResourcesServices $resourcesServices)
    {
        $this->resourcesServices = $resourcesServices;
    }

    public function uploadResource(Request $request)
    {
        return $this->resourcesServices->uploadResource($request->file('file_data')); 
    }

}