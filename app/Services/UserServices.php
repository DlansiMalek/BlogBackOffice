<?php

namespace App\Services;

use App\Metiers\Utils;
use App\Models\Congress_User;
use App\Models\User;
use PDF;
use Illuminate\Support\Facades\Mail;

class UserServices
{

    public function getAllUsers()
    {
        return User::with(['city', 'city.country'])->get();
    }

    public function registerUser($request)
    {
        $email = $request->input('email');

        $user = User::where('email', 'like', $email)->first();

        if ($user) {
            if ($existCongress = $this->isExistCongress($user, $request->input("congressId"))) {
                return null;
            } else {
                $this->settingInCongress($user, $request->input("congressId"));
                return $user;
            }
        } else {
            $newUser = new User();
            $newUser->first_name = $request->input('first_name');
            $newUser->last_name = $request->input('last_name');
            if ($request->has('gender'))
                $newUser->gender = $request->input('gender');
            if ($request->has('establishment'))
                $newUser->establishment = $request->input('establishment');
            if ($request->has('profession'))
                $newUser->profession = $request->input('profession');
            if ($request->has('tel'))
                $newUser->tel = $request->input('tel');
            $newUser->mobile = $request->input('mobile');
            if ($request->has('fax'))
                $newUser->fax = $request->input('fax');
            if ($request->has('address'))
                $newUser->address = $request->input('address');
            if ($request->has('postal'))
                $newUser->postal = $request->input('postal');
            if ($request->has('domain'))
                $newUser->domain = $request->input('domain');
            if ($request->has('city_id'))
                $newUser->city_id = $request->input('city_id');
            $newUser->email = $email;
            if ($request->has('cin'))
                $newUser->cin = $request->input('cin');
            $newUser->valide = false;
            $newUser->validation_code = str_random(40);
            $newUser->save();

            /* Generation QRcode */
            $qrcode = Utils::generateCode($newUser->id_User);
            $newUser->qr_code = $qrcode;
            $newUser->save();

            $this->settingInCongress($newUser, $request->input("congressId"));

            return $newUser;
        }
    }

    public function sendConfirmationMail($user)
    {
        $link = "https://congress-api.vayetek.com/api/users/" . $user->id_User . "/validate/" . $user->validation_code;
        $email = $user->email;
        Mail::send('validationEmail', ['nom' => $user->last_name,
            'prenom' => $user->first_name, 'CIN' => $user->cin,
            'carte_Etudiant' => $user->carte_Etudiant, 'link' => $link], function ($message) use ($email) {
            $message->to($email)->subject('Validation du compte');
        });
    }

    public function getParticipatorById($user_id)
    {
        return User::find($user_id);
    }

    public function updateUser($request, $updateUser)
    {
        if (!$updateUser) {
            return null;
        }
        $updateUser->first_name = $request->input('first_name');
        $updateUser->last_name = $request->input('last_name');
        $updateUser->gender = $request->input('gender');
        $updateUser->establishment = $request->input('establishment');
        $updateUser->profession = $request->input('profession');
        $updateUser->tel = $request->input('tel');
        $updateUser->mobile = $request->input('mobile');
        $updateUser->fax = $request->input('fax');
        $updateUser->address = $request->input('address');
        $updateUser->postal = $request->input('postal');
        $updateUser->domain = $request->input('domain');
        $updateUser->city_id = $request->input('city_id');
        $updateUser->update();
        return $updateUser;
    }

    public function impressionBadge($user)
    {

        $data = [
            "name" => $user->first_name . " " . $user->last_name
        ];
        $pdf = PDF::loadView('pdf.badge', $data);
        return $pdf->save(public_path() . "/badge/invitation.pdf");
    }

    public function sendMail($user, $congress)
    {
        $email = $user->email;
        $pathToFile = public_path() . "/badge/invitation.pdf";
        Mail::send('emailInscription', ['nom' => $user->last_name,
            'prenom' => $user->first_name, 'congressName' => $congress->name
        ], function ($message) use ($email, $congress, $pathToFile) {
            $message->attach($pathToFile);
            $message->to($email)->subject($congress->name);
        });
    }

    public function getAllPresentParticipatorByCongress($congressId)
    {
        return User::join("Congress_User", "Congress_User.id_User", "=", "User.id_User")
            ->where("Congress_User.isPresent", "=", 1)
            ->where("id_Congress", "=", $congressId)
            ->orderBy("Congress_User.updated_at", "desc")
            ->get();
    }


    public function getAllParticipatorByCongress($congressId)
    {
        return User::join("Congress_User", "Congress_User.id_User", "=", "User.id_User")
            ->where("id_Congress", "=", $congressId)
            ->get();
    }

    public function sendingToOrganisateur($participator, $congressId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            'http://137.74.165.25:3002/api/congress/users/send-present', [
                'form_params' => [
                    'user' => json_decode(json_encode($participator)),
                    'congressId' => $congressId
                ]
            ]);

        return json_decode($res->getBody(), true);
    }

    public function sendingToAdmin($allParticipants, $congressId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            'http://137.74.165.25:3002/api/congress/users/send-all', [
                'form_params' => [
                    'users' => json_decode(json_encode($allParticipants)),
                    'congressId' => $congressId
                ]
            ]);

        return json_decode($res->getBody(), true);
    }

    public function getParticipatorByQrCode($qr_code)
    {
        return User::where('qr_code', 'like', $qr_code)->first();
    }

    public function affectUserToCongress($congressId, $user, $isPresent, $hasPaid)
    {
        $congressUser = Congress_User::where("id_User", "=", $user->id_User)
            ->where("id_Congress", "=", $congressId)
            ->first();

        if ($congressUser->isPresent != 1 && $isPresent == 1) {
            $this->sendingToOrganisateur($user, $congressId);
        }
        if ($congressUser) {
            $congressUser->isPresent = $isPresent;
            $congressUser->isPaid = $hasPaid;
            $congressUser->update();
        }

        return $congressUser;
    }

    public function getParticipatorByIdByCongress($userId, $congressId)
    {
        return User::
        withCount(['congresses as isPresent' => function ($query) use ($congressId) {
            $query->where("Congress_User.id_Congress", "=", $congressId)
                ->where("Congress_User.isPresent", "=", 1);
        }])->
        withCount(['congresses as isPaid' => function ($query) use ($congressId) {
            $query->where("Congress_User.id_Congress", "=", $congressId)
                ->where("Congress_User.isPaid", "=", 1);;
        }])->where("id_User", "=", $userId)
            ->first();

    }

    private function isExistCongress($user, $congressId)
    {
        return Congress_User::where("id_User", "=", $user->id_User)
            ->where("id_Congress", "=", $congressId)->first();
    }

    private function settingInCongress($user, $congressId)
    {
        $user_congress = new Congress_User();
        $user_congress->id_User = $user->id_User;
        $user_congress->id_Congress = $congressId;
        $user_congress->save();
        return $user_congress;
    }
}