<?php

namespace App\Services;

use App\Models\User;
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
        $this->sendConfirmationMail($newUser);
        return $newUser;
    }

    public function sendConfirmationMail($user)
    {
        $link = "http://localhost/api/users" . $user->user_id . "/validate/" . $user->validation_code;
        $email = $user->email;
        Mail::send('validationEmail', ['nom' => $user->first_name,
            'prenom' => $user->last_name, 'CIN' => $user->cin,
            'carte_Etudiant' => $user->carte_Etudiant, 'link' => $link], function ($message) use ($email) {
            $message->to($email)->subject('Validation du compte');
        });
    }

    public function getUserById($user_id)
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
}