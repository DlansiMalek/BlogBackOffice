<?php

namespace App\Http\Controllers;

use App\Metiers\AdminServices;
use App\Metiers\CongressServices;
use App\Metiers\Utils;
use App\Models\Congress_User;
use App\Models\Inscription_Neuro2018;
use App\Models\User;
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

        if (!$congress_user = $this->userServices->affectUserToCongress($request->input("congressId"), $participator, $request->input('isPresent'), $request->input('hasPaid'))) {
            return response()->json(['response' => 'participator not participated in this congress'], 404);
        }
        //$allPresents = $this->userServices->getAllPresentParticipatorByCongress($request->input("congressId"));


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
        $users = User::where('id_User', '>', 297)->get();
        /*foreach ($users as $user) {
            $userCongress = Congress_User::where('id_User', '=', $user->id_User)->first();
            if (is_null($userCongress)) {
                Congress_User::create([
                    'id_User' => $user->id_User,
                    'id_Congress' => 4
                ])->save();
            }
        }*/
        foreach ($users as $user) {
            $userCongress = Congress_User::where('id_User', '=', $user->id_User)->first();
            if (is_null($userCongress)) {
                Congress_User::create([
                    'id_User' => $user->id_User,
                    'id_Congress' => 4
                ])->save();
            }
        }
        return response()->json(['response' => 'all user congresses updated'], 200);
    }

    public function updateUsers()
    {
        $users = Inscription_Neuro2018::all();
        foreach ($users as $user) {
            $userNew = User::create([
                'first_name' => $user->prenom,
                'last_name' => $user->nom,
                'profession' => $user->status,
                'email' => $user->email,
                'address' => $user->adresse,
                'mobile' => $user->tel,
                'transport' => $user->transport,
                'repas' => $user->repas,
                'diner' => $user->diner,
                'hebergement' => $user->hebergement,
                'chambre' => $user->chambre,
                'conjoint' => $user->conjoint,
                'date_arrivee' => $user->date_arrivee,
                'date_depart' => $user->date_depart,
                'date' => $user->date,
                'qr_code' => $user->qr_code
            ])->save();
        }
        return response()->json(['response' => 'all users updated'], 200);
    }

    public function generateUserQrCode()
    {
        set_time_limit(3600);
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
        ini_set("memory_limit", "-1");
        set_time_limit(3600);

        $users = $this->userServices->getUsersByCongress(4);
        File::cleanDirectory(public_path() . '/badge/neuro');
        $itemCount=6;
        for ($i = 0; $i < sizeof($users) / $itemCount; $i++) {
            $tempUsers = array_slice($users, $i * $itemCount, $itemCount);

            $j = 1;
            $pdfFileName = '';
            foreach ($tempUsers as $tempUser) {
                Utils::generateQRcode($tempUser['qr_code'], 'qrcode_' . $j);
                $pdfFileName .= '_' . $tempUser['id_User'];
                $j++;
            }
            $data = [
                'users' => json_decode(json_encode($tempUsers), false)];

            $pdf = PDF::loadView('pdf.badges-09-03', $data);
            return $pdf->stream('badges-09-03.pdf');
            $pdf->save(public_path() . '/badge/neuro/badges' . $pdfFileName . '.pdf');
        }
        $files = glob(public_path() . '/badge/neuro/*');
        Zipper::make(public_path() . '/badge/neuro/neuro_badges.zip')->add($files)->close();
        return response()->download(public_path() . '/badge/neuro/neuro_badges.zip');
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

    public function generateTickets()
    {
        set_time_limit(3600);
        for ($i = 231; $i <= 400; $i++) {
            User::create([
                "first_name" => "Ticket",
                "last_name" => $i,
            ])->save();
        }
        for ($i = 1; $i <= 100; $i++) {
            User::create([
                "first_name" => "Invitation",
                "last_name" => $i,
            ])->save();
        }
        return response()->json(['response' => 'tickets registred'], 200);
    }
}
