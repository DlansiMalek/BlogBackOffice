<?php

namespace App\Services;

use App\Metiers\Utils;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Mail;

class UserServices
{

    public function getAllUsers()
    {
        return User::with(['city', 'city.country'])->get();
    }

    public function registerUser($request)
    {
        $cin = $request->input('cin');
        $email = $request->input('email');

        $existUser = User::where('cin', 'like', $cin)->orWhere('email', 'like', $email)->first();
        if ($existUser) {
            return null;
        }


        $newUser = new User();
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        $newUser->gender = $request->input('gender');
        $newUser->establishment = $request->input('establishment');
        $newUser->profession = $request->input('profession');
        $newUser->tel = $request->input('tel');
        $newUser->mobile = $request->input('mobile');
        $newUser->fax = $request->input('fax');
        $newUser->address = $request->input('address');
        $newUser->postal = $request->input('postal');
        $newUser->domain = $request->input('domain');
        $newUser->city_id = $request->input('city_id');
        $newUser->email = $email;
        $newUser->cin = $cin;
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
}