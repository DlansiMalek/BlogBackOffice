<?php

namespace App\Http\Controllers;

use App\Metiers\AdminServices;
use App\Metiers\CongressServices;
use App\Metiers\Utils;
use App\Models\Congress_User;
use App\Models\User;
use App\Models\User_Tmp;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use PDF;
use Zipper;

class AdminController extends Controller
{
    protected $userServices;
    protected $adminServices;
    protected $congressService;

    public function __construct(UserServices $userServices,
                                AdminServices $adminServices,
                                CongressServices $congressService)
    {
        $this->userServices = $userServices;
        $this->adminServices = $adminServices;
        $this->congressService = $congressService;
    }

    public function scanParticipatorQrCode(Request $request)
    {
        if (!$request->has(['qrcode', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['qrcode']], 400);
        }
        $qrcode = $request->input('qrcode');
        if (strlen($qrcode) < 7) {
            //return response()->json(['resposne' => 'bad qrcode'], 400);
        }
        $participator = $this->userServices->getParticipatorByQrCode($request->input('qrcode'));
        $participator = $this->userServices->getParticipatorByIdByCongress($participator->id_User, $request->input("congressId"));
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }
        return $participator;
    }

    public function updateParticipatorStatus(Request $request, $id_Participator)
    {
        if (!$request->has(['isPresent', 'hasPaid', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['isPresent', 'hasPaid']], 400);
        }
        $participator = $this->userServices->getParticipatorById($id_Participator);
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }

        if (!$congress_user = $this->userServices->affectUserToCongress($request->input("congressId"), $participator->id_User, $request->input('isPresent'), $request->input('hasPaid'))) {
            return response()->json(['response' => 'participator not participated in this congress'], 404);
        }
        //$allPresents = $this->userServices->getAllPresentParticipatorByCongress($request->input("congressId"));

        $this->userServices->sendingToOrganisateur($participator, $request->input("congressId"));

        /*$allParticipants = $this->userServices->getAllParticipatorByCongress($request->input("congressId"));

        $this->userServices->sendingToAdmin($allParticipants, $request->input("congressId"));*/

        return response()->json(["message" => "success sending and scaning"], 200);
    }

    public function getAuhentificatedAdmin()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $admin = $this->adminServices->getAdminById($admin->id_Admin);

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('admin'));
    }


    public function getAdminCongresses()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        return $this->adminServices->getAdminCongresses($admin->id_Admin);
    }

    public function getAllParticipantsByCongress($congressId)
    {
        $participants = $this->userServices->getAllParticipatorByCongress($congressId);

        return response()->json($participants, 200);

    }

    public function getAllPresenceByCongress($congressId)
    {
        $presences = $this->userServices->getAllPresentParticipatorByCongress($congressId);

        return response()->json($presences, 200);

    }

    public function updateUserWithCongress()
    {
        set_time_limit(3600);
        $users = User::all();
        foreach ($users as $user) {
            $userCongress = Congress_User::where('id_User', '=', $user->id_User)->first();
            if (is_null($userCongress)) {
                Congress_User::create([
                    'id_User' => $user->id_User,
                    'id_Congress' => 1,
                    'Mode_exercice' => $user->Mode_exercice,
                    'pack' => $user->pack,
                    //'reservation' => $user->reservation,
                    //'atelier' => $user->atelier,
                    //'Mode_payement' => $user->Mode_payement,
                    'Mode_exercice' => $user->Mode_exercice,
                    'prix_pack' => $user->prix_pack,
                    'laboratoire' => $user->laboratoire,
                    //'prix_reservation' => $user->prix_reservation,
                    //'prix_total' => $user->prix_total,
                ])->save();
            }
        }
        return response()->json(['response' => 'all user congresses updated'], 200);
    }

    public function updateUsers()
    {
        $users = User_Tmp::all();
        foreach ($users as $user) {
            $userNew = User::create([
                'id_User' => $user->id_User,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'Mode_exercice' => $user->Mode_exercice,
                'pack' => $user->pack,
                'laboratoire' => $user->laboratoire,
                'city' => $user->city
            ])->save();
        }
        return response()->json(['response' => 'all users updated'], 200);
    }

    public function generateUserQrCode()
    {
        $users = User::all();
        foreach ($users as $user) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $user->qr_code = $randomString;
            $user->update();
        }
    }

    public function generateBadges()
    {
        set_time_limit(3600);
        $users = User::all()->toArray();
        File::cleanDirectory(public_path() . '/badge/jnn');
        for ($i = 0; $i < sizeof($users) / 6; $i++) {
            $tempUsers = array_slice($users, $i * 6, 6);
            $j = 1;
            $pdfFileName = '';
            foreach ($tempUsers as $tempUser) {
                Utils::generateQRcode($tempUser['qr_code'], 'qrcode_' . $j);
                $pdfFileName .= '_' . $tempUser['id_User'];
                $j++;
            }
            $data = [
                'users' => json_decode(json_encode($tempUsers), false)];
            $pdf = PDF::loadView('pdf.badges', $data);
            return $pdf->stream('badges.pdf');
            $pdf->save(public_path() . '/badge/jnn/badges' . $pdfFileName . '.pdf');
        }
        $files = glob(public_path() . '/badge/jnn/*');
        Zipper::make(public_path() . '/badge/jnn/jnn_badges.zip')->add($files)->close();
        return response()->download(public_path() . '/badge/jnn/jnn_badges.zip');
        //return $pdf->stream('badges.pdf');
    }

    public function cleanBadges()
    {
        File::cleanDirectory(public_path() . '/badge/jnn');
        return response()->json(["message" => "Badges deleted"]);
    }

    public function updatePaiedParticipator($userId, Request $request)
    {
        if (!$request->has(['status', 'congressId'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['status', 'congressId']], 400);
        }
        if (!$congressUser = $this->adminServices->updateStatusPaied($userId, $request->input("status"), $request->input("congressId"))) {
            return response()->json(["error" => "User not inscrit Congress"]);
        }

        return response()->json(["message" => "status update success"]);

    }
}
