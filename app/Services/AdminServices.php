<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Metiers;


use App\Models\Admin;
use JWTAuth;

class AdminServices
{

    public function getAdminByLogin($login)
    {
        return Admin::whereEmail($login)
            ->with(["privileges"])
            ->first();

    }

    public function retrieveAdminFromToken()
    {
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

    public function getAdminById($id_Admin)
    {
        return Admin::where("id_Admin", "=", $id_Admin)
            ->with(["privileges"])
            ->first();
    }

}