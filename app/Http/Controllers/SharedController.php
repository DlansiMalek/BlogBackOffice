<?php

namespace App\Http\Controllers;


use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use http\Env\Request;
use Illuminate\Support\Facades\Log;

class SharedController extends Controller
{

    protected $sharedServices;
    protected $userServices;


    function __construct(SharedServices $sharedServices,
                         UserServices $userServices)
    {
        $this->sharedServices = $sharedServices;
        $this->userServices = $userServices;
    }

    public function getAllPrivileges()
    {
        return response()->json($this->sharedServices->getAllPrivileges());
    }

    public function getPrivilegesWithBadges()
    {
        return response()->json($this->sharedServices->getPrivilegesWithBadges());
    }


    public function getRecuPaiement($path)
    {
        $chemin = config('media.payement-user-recu');
        return response()->download(storage_path('app/' . $chemin . "/" . $path));
    }

    public function getAllTypesAttestation()
    {
        return response($this->sharedServices->getAllTypesAttestation());
    }

    public function getAllCountries()
    {
        return response()->json($this->sharedServices->getAllCountries());
    }

    public function getFile($file_path)
    {
        return response()->file('../storage/app/mail-images/' . $file_path);
    }

    public function encrypt($password)
    {
        return bcrypt($password);
    }

    function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }


    public function scanAllPresence(\Illuminate\Http\Request $request)
    {

        $date = $request->input("date");

        $users = $this->userServices->getUserModifiedDate($date);
        Log::info(sizeof($users));
        //Log::info(json_encode($users, true));

        //return response()->json(['message'=> 'scan all presence']);

        foreach ($users as $user) {
            if (sizeof($user->user_congresses) > 0) {
                foreach ($user->user_congresses as $user_congress) {
                    $user_congress->isPresent = 1;
                    $user_congress->update();
                }
            }
        }
    }

    public function deleteOldQrCode()
    {
        $users = $this->userServices->getAllUsers();
        $groupedUsers = Utils::groupBy('qr_code', json_decode($users, true));

        foreach ($groupedUsers as $groupedUser) {
            if (sizeof($groupedUser) > 1) {
                //Log::info($groupedUser);
                for ($i = 0; $i < (sizeof($groupedUser) - 1); $i++) {
                    $this->userServices->updateQrCode($groupedUser[$i]['user_id'], Utils::generateCode($groupedUser[$i]['user_id']));
                }
            }
        }


    }

    public function synchroData()
    {


        $users = $this->userServices->getAllUsers();
        $groupedUser = Utils::groupBy('email', json_decode($users, true));

        foreach ($groupedUser as $groupeUser) {
            if (sizeof($groupeUser) > 1) {
                $fixUser = $groupeUser[0];
                for ($i = 1; $i < sizeof($groupeUser); $i++) {
                    $this->userServices->updateUserIdUserCongress($groupeUser[$i]['user_id'], $fixUser['user_id']);
                    $this->userServices->deleteById($groupeUser[$i]['user_id']);
                }
            }
        }
        return response()->json(['message' => 'synchro done']);
    }

    public function getAllCongressTypes()
    {
        return response()->json($this->sharedServices->getAllCongressTypes());


    }
}
