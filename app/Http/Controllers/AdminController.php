<?php

namespace App\Http\Controllers;

use App\Metiers\AdminServices;
use App\Metiers\CongressServices;
use App\Models\Congress_User;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use PDF;

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
        $allPresents = $this->userServices->getAllPresentParticipatorByCongress($request->input("congressId"));

        $this->userServices->sendingToOrganisateur($allPresents, $request->input("congressId"));

        $allParticipants = $this->userServices->getAllParticipatorByCongress($request->input("congressId"));

        $this->userServices->sendingToAdmin($allParticipants, $request->input("congressId"));

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
                    'reservation' => $user->reservation,
                    'atelier' => $user->atelier,
                    'Mode_payement' => $user->Mode_payement,
                    'prix_pack' => $user->prix_pack,
                    'prix_reservation' => $user->prix_reservation,
                    'prix_total' => $user->prix_total,
                ])->save();
            }
        }
        return response()->json(['response' => 'all user congresses updated'], 200);
    }

    public function generateBadges()
    {
        $data = [
            'users' => User::all()
        ];
        $pdf = PDF::loadView('pdf.badges', $data);
        return $pdf->stream('badges.pdf');
    }
}
