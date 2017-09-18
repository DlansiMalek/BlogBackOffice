<?php

namespace App\Http\Controllers;

use App\Services\UserServices;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $userServices;

    public function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
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
        if (!$request->has(['isPresent', 'hasPaid'])) {
            return response()->json(['resposne' => 'bad request', 'required fields' => ['isPresent', 'hasPaid']], 400);
        }
        $participator = $this->userServices->getParticipatorById($id_Participator);
        if (!$participator) {
            return response()->json(['resposne' => 'participator not found'], 404);
        }
        $participator->isPresent = $request->input('isPresent');
        $participator->hasPaid = $request->input('hasPaid');
        $participator->update();
        return $participator;
    }
}
