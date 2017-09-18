<?php

namespace App\Metiers;

use App\Models\Congress;
use Illuminate\Support\Facades\Config;
use JWTAuth;

class CongressServices
{

    public function getCongressById($id_Congress)
    {
        return Congress::find($id_Congress);
    }

    function retrieveCongressFromToken()
    {
        Config::set('jwt.user', 'App\Models\Congress');
        Config::set('jwt.identifier', 'id_Congress');
        try {
            return JWTAuth::parseToken()->toUser();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }
}