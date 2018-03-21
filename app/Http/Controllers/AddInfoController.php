<?php

namespace App\Http\Controllers;


use App\Services\AddInfoServices;

class AddInfoController extends Controller
{

    protected $addInfoServices;


    function __construct(AddInfoServices $addInfoServices)
    {
        $this->addInfoServices = $addInfoServices;
    }


    /**
     * @SWG\Get(
     *   path="/add-info/type/list",
     *   summary="Get All Type Info",
     *   operationId="getAllTypesInfo",
     *   security={
     *     {"Bearer": {}}
     *   },
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    function getAllTypesInfo()
    {
        $typesInfo = $this->addInfoServices->getAllTypesInfo();

        return response()->json($typesInfo);
    }


}
