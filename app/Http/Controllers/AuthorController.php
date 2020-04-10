<?php

namespace App\Http\Controllers;

use App\Services\AuthorServices;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    protected $authorServices;

    function __construct(AuthorServices $authorServices)
    {
        $this->authorServices=$authorServices;
    }

    public function saveAuthor(Request $request){

        if (!($request->has('first_name')&& $request->has('last_name')&&$request->has('rank'))){
            return response()->json(['Response'=>'bad request'],400);
        }

        return $this->authorServices->saveAuthor($request->input('first_name'),$request->input('last_name'),$request->input('rank'),$request->input('congress_id'),$request->input('service_id'),$request->input('etablissement_id'));


    }
}
