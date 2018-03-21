<?php

namespace App\Http\Controllers;


use App\Services\AccessServices;

class AccessController extends Controller
{

    protected $accessServices;


    function __construct(AccessServices $accessServices)
    {
        $this->accessServices = $accessServices;
    }


    /**
     * @SWG\Get(
     *   path="/access/type/list",
     *   summary="Get All Type Of Access",
     *   operationId="getAllTypesAccess",
     *   security={
     *     {"Bearer": {}}
     *   },
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    function getAllTypesAccess()
    {
        $typesAccess = $this->accessServices->getAllTypesAccess();

        return response()->json($typesAccess);
    }


}
