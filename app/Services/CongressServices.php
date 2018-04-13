<?php

namespace App\Services;

use App\Models\Congress;
use Illuminate\Support\Facades\Config;
use JWTAuth;

class CongressServices
{

    public function getCongressById($id_Congress)
    {
        return Congress::with(["badge", "responsibles", "accesss.responsibles", "add_infos"])
            ->where("congress_id", "=", $id_Congress)
            ->first();
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

    public function getCongressAllAccess($adminId)
    {
        return Congress::with(["accesss.responsibles", "accesss.type_access"])
            ->where("admin_id", "=", $adminId)
            ->get();
    }

    public function getCongressAllowedAccess($adminId)
    {
        return Congress::with(["accesss.responsibles", "accesss.type_access"])->where(function ($q) use ($adminId) {
            $q->whereHas("accesss.responsibles", function ($query) use ($adminId) {
                $query->where("admin_id", "=", $adminId);
            });
        })->get();

    }

}