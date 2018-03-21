<?php

namespace App\Services;

use App\Models\Congress;
use Illuminate\Support\Facades\Config;
use JWTAuth;

class CongressServices
{

    public function getCongressById($id_Congress)
    {
        return Congress::with(["responsibles", "accesss", "add_infos"])
            ->where("congress_id", "=", $id_Congress)
            ->get();
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

    public function addCongress($name, $date, $admin_id)
    {
        $congress = new Congress();
        $congress->name = $name;
        $congress->date = $date;
        $congress->admin_id = $admin_id;
        $congress->save();
        return $congress;
    }

}