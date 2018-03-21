<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Admin;
use App\Models\Admin_Congress;
use App\Models\Congress;
use App\Models\Congress_User;
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
            return auth()->user();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $refreshed = JWTAuth::refresh(JWTAuth::getToken());
            $user = JWTAuth::setToken($refreshed)->toUser();
            header('Authorization: Bearer ' . $refreshed);
            return $user;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }

    public function getAdminById($admin_id)
    {
        return Admin::where("admin_id", "=", $admin_id)
            ->with(["privileges", 'congresses'])
            ->first();
    }

    public function getAdminCongresses($admin_id)
    {
        return Congress::where("admin_id", "=", $admin_id)
            ->get();
    }

    public function updateStatusPaied($userId, $status, $congressId)
    {

        $userCongress = Congress_User::where("id_User", "=", $userId)
            ->where("id_Congress", "=", $congressId)
            ->first();

        if ($userCongress) {
            $userCongress->isPaid = $status;
            $userCongress->update();
        }
        return $userCongress;
    }

    public function getListPersonelsByAdmin($admin_id)
    {
        return Admin::where("responsible", "=", $admin_id)
            ->get();
    }

    public function addResponsibleCongress($responsibleIds, $congress_id)
    {
        foreach ($responsibleIds as $responsibleId) {
            $congressAdmin = new Admin_Congress();
            $congressAdmin->admin_id = $responsibleId;
            $congressAdmin->congress_id = $congress_id;
            $congressAdmin->save();
        }
    }

}