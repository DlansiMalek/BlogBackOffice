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
use App\Models\Privilege;
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

    public function getListPersonelsByAdmin($congress_id)
    {
        return Admin::whereHas('admin_congresses', function ($query) use ($congress_id) {
            $query->where('congress_id', '=', $congress_id);
        })
            ->with(['admin_congresses' => function ($query) use ($congress_id) {
                $query->where('congress_id', '=', $congress_id)
                    ->first();
            }])
            ->get();
    }

    public function getPersonelsByIdAndCongressId($congress_id,$admin_id)
    {
        return Admin::where('admin_id','=',$admin_id)
//        ->whereHas('admin_congresses', function ($query) use ($congress_id) {
//            $query->where('congress_id', '=', $congress_id);
//        })
            ->with(['admin_congresses' => function ($query) use ($congress_id,$admin_id) {
                $query->where('congress_id', '=', $congress_id)
                    ->where('admin_id','=',$admin_id)
                    ->first();
            }])
            ->first();
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

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateNewPassword(Admin $admin) {
        $newPassword =  $this->generateRandomString(20);
        AdminCongress::where('admin_id','=',$admin->admin_id)
            ->update(['passwordDecrypt' => $newPassword,
                'password' => bcrypt($newPassword)]);
        return $newPassword;
    }

    public function sendForgetPasswordEmail(Admin $admin) {


    }

    public function addPersonnel($admin)
    {
        $personnel = new Admin();
        $personnel->name = $admin["name"];
        $personnel->email = $admin["email"];
        $personnel->mobile = $admin["mobile"];

        $password = str_random(8);
        $personnel->passwordDecrypt = $password;
        $personnel->password = bcrypt($password);

        $personnel->save();

        return $personnel;
    }
    public function editPersonnel($admin)
    {
        return Admin::where("admin_id", "=", $admin['admin_id'])
            ->update(['name' => $admin["name"],
                    'email' => $admin["email"],
                    'mobile' => $admin["mobile"]]);

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