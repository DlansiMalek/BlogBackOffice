<?php

namespace App\Http\Controllers;

use App\Metiers\AdminServices;
use App\Services\UserServices;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $userServices;
    protected $adminServices;

    public function __construct(UserServices $userServices,
                                AdminServices $adminServices)
    {
        $this->userServices = $userServices;
        $this->adminServices = $adminServices;
    }

    public function scanParticipatorQrCode(Request $request)
    {
        if (!$request->has(['qrcode'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['qrcode']], 400);
        }
        $qrcode = $request->input('qrcode');
        if (strlen($qrcode) < 7) {
            return response()->json(['resposne' => 'bad qrcode'], 400);
        }
        $participator = $this->userServices->getParticipatorById(substr($request->input('qrcode'), 6));
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
        $participator->isPresent = $request->input('isPresent');
        $participator->hasPaid = $request->input('hasPaid');
        $participator->update();

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


    public function getAdminCongresses(){
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        return $this->adminServices->getAdminCongresses($admin->id_Admin);
    }
}
