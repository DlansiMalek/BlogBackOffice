<?php

namespace App\Services;

use App\Metiers\Utils;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Log;
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

        $existUser = User::where('email', 'like', $email)->first();
        if ($existUser) {
            return null;
        }


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

        return $newUser;
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
        return $pdf->save(public_path() . "/badge/badge.pdf");
    }

    public function sendMail($user)
    {
        $email = $user->email;
        $pathToFile = public_path() . "/badge/badge.pdf";
        $link = "https://congress-api.vayetek.com/api/users/" . $user->id_User . '/validate/' . $user->validation_code;
        Mail::send('emailInscription', ['nom' => $user->last_name,
            'prenom' => $user->first_name, 'CIN' => $user->cin,
            'link' => $link,
            'carte_Etudiant' => $user->carte_Etudiant], function ($message) use ($email, $pathToFile) {
            $message->attach($pathToFile);
            $message->to($email)->subject('Validation du compte');
        });
    }

    public function getAllPresentParticipatorByCongress($congressId)
    {
        return User::join("Congress_User", "Congress_User.id_User", "=", "User.id_User")
            ->where("isPresent", "=", 1)
            ->where("id_Congress", "=", $congressId)
            ->get();
    }


    public function getAllParticipatorByCongress($congressId)
    {
        return User::join("Congress_User", "Congress_User.id_User", "=", "User.id_User")
            ->where("id_Congress", "=", $congressId)
            ->get();
    }

    public function sendingToOrganisateur($allPresents, $congressId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            'http://localhost:3000/api/congress/users/send-present', [
                'form_params' => [
                    'users' => json_decode(json_encode($allPresents)),
                    'congressId' => $congressId
                ]
            ]);

        return json_decode($res->getBody(), true);
    }

    public function sendingToAdmin($allParticipants, $congressId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            'http://localhost:3000/api/congress/users/send-all', [
                'form_params' => [
                    'users' => json_decode(json_encode($allParticipants)),
                    'congressId' => $congressId
                ]
            ]);

        return json_decode($res->getBody(), true);
    }
}