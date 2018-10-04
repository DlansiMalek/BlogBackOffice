<?php

namespace App\Services;

use App\Models\Congress;
use App\Models\Organization;
use App\Models\User;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JWTAuth;
use PDF;

/**
 * @property OrganizationServices $organizationServices
 */
class CongressServices
{


    public function __construct(OrganizationServices $organizationServices)
    {
        $this->organizationServices = $organizationServices;
    }

    public function getCongressById($id_Congress)
    {
        return Congress::with(["badges", "users", "attestation", "accesss.participants", "accesss.attestation", "accesss"])
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
        return Congress::with(["accesss"])
            ->where("admin_id", "=", $adminId)
            ->get();
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

    public function editCongress($congress, $adminId, $request)
    {
        $congress->name = $request->input("name");
        $congress->date = $request->input("date");
        $congress->admin_id = $adminId;

        $congress->update();

        return $congress;
    }

    public function getUsersByStatus($congressId, int $status)
    {
        return User::with(['grade'])
            ->where('isPresent', '=', $status)
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getLabsByCongress($congressId)
    {
        return Organization::with(['users' => function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        }])->whereHas('users', function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        })->get();
    }

    public function getOrganizationInvoiceByCongress($labId, $congressId)
    {
        $lab = $this->organizationServices->getOrganizationById($labId);
        $participants = User::where('organization_id', $labId)->where('congress_id', '=', $congressId)->get();
        $totalPrice = 0;
        foreach ($participants as $participant) {
            $totalPrice += $participant->price;
        }

        $data = [
            'lab' => $lab,
            'participants' => $participants,
            'totalPrice' => $totalPrice,
            'displayTaxes' => false
        ];
        $path = public_path() . "/facture";
        $pdf = PDF::loadView('pdf.invoice.invoice', $data);
        $pdf->save($path . '/facture.pdf');
        return response()->download($path . '/facture.pdf')->deleteFileAfterSend(true);
    }

    public function getBadgeByPrivilegeId($congress, $privilege_id)
    {
        for ($i = 0; $i < sizeof($congress->badges); $i++) {
            if ($congress->badges[$i]->privilege_id == $privilege_id) {
                return $congress->badges[$i]->badge_id_generator;
            }
        }
        return null;
    }

}
