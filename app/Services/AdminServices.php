<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Admin;
use App\Models\AdminCongress;
use App\Models\Congress;
use Illuminate\Http\Request;
use JWTAuth;

class AdminServices
{

    public function getAdminByLogin($login)
    {
        return Admin::whereEmail($login)
            ->with(["congresses"])
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
            ->with(['admin_congresses.congress', 'admin_congresses.privilege'])
            ->first();
    }

    public function getAdminCongresses(Admin $admin)
    {
        return Congress::whereHas('admin_congresses', function ($query) use ($admin) {
            $query->where('admin_id', '=', $admin->admin_id);
        })->get();
    }

    public function getListPersonelsByAdmin($admin_id)
    {
        return Admin::where("responsible", "=", $admin_id)
            ->whereHas('privileges', function ($query) {
                $query->where('privilege_id', '=', 2);
            })
            ->with(['congress_responsible.badges'])
            ->get();
    }

    public function addResponsibleCongress($responsibleIds, $congress_id)
    {
        foreach ($responsibleIds as $responsibleId) {
            $congressAdmin = new AdminCongress();
            $congressAdmin->admin_id = $responsibleId;
            $congressAdmin->congress_id = $congress_id;
            $congressAdmin->save();
        }
    }

    public function addPersonnel(Request $request, $admin_id)
    {
        $personnel = new Admin();
        $personnel->name = $request->input("name");
        $personnel->email = $request->input("email");
        $personnel->mobile = $request->input("mobile");

        $personnel->responsible = $admin_id;

        $password = str_random(8);
        $personnel->passwordDecrypt = $password;
        $personnel->password = bcrypt($password);

        $personnel->save();

        return $personnel;
    }

    public function deleteAdminById($admin)
    {
        $admin->delete();
    }

    public function getAdminByQrCode($QrCode)
    {
        //TODO Fixing with the new Design
        $admin = Admin::where("passwordDecrypt", "=", $QrCode)
            ->first();
        $admin->admin = count(Admin_Privilege::where('admin_id', '=', $admin['admin_id'])->where('privilege_id', '=', 1)->get()) > 0;
        return $admin;
    }

    public function getConnectedAdmin(Request $request)
    {
        if (!request()->user()) return null;
        if (!$admin = $this->getAdminById($request->user()['admin_id'])) return null;
        return $admin;
    }

}