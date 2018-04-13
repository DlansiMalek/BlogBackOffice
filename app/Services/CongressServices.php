<?php

namespace App\Services;

use App\Models\Congress;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Config;
use JWTAuth;
use Illuminate\Filesystem\Filesystem;
use PDF;
use Swagger\Util;
use Illuminate\Support\Facades\File;

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

    public function getBadgesByUsers($badgeName, $users)
    {

        $users = $users->toArray();
        $file = new Filesystem();
        $path = public_path() . "/" . $badgeName;

        if (!$file->exists($path)) {
            $file->makeDirectory($path);
        }
        $qrCodePath = "/QrCode";
        if (!$file->exists(public_path() . $qrCodePath)) {
            $file->makeDirectory(public_path() . $qrCodePath);
        }

        File::cleanDirectory($path);
        for ($i = 0; $i < sizeof($users) / 4; $i++) {
            $tempUsers = array_slice($users, $i * 4, 4);
            $j = 1;
            $pdfFileName = '';
            foreach ($tempUsers as $tempUser) {
                Utils::generateQRcode($tempUser['qr_code'], $qrCodePath . '/qr_code_' . $j . '.png');
                $pdfFileName .= '_' . $tempUser['user_id'];
                $j++;
            }
            $data = [
                'users' => json_decode(json_encode($tempUsers), false)];
            $pdf = PDF::loadView('pdf.' . $badgeName, $data);
            $pdf->save($path . '/badges' . $pdfFileName . '.pdf');
        }
        $files = glob($path . '/*');
        $file->deleteDirectory(public_path() . $qrCodePath);
        Zipper::make($path . '/badges.zip')->add($files)->close();
        return response()->download($path . '/badges.zip')->deleteFileAfterSend(true);

    }

}