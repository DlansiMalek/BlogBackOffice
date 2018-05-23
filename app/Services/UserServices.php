<?php

namespace App\Services;

use App\Models\Payement_Type;
use App\Models\User;
use App\Models\Congress;
use App\Models\User_Access;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

class UserServices
{

    public function getAllUsers()
    {
        return User::with(['city', 'city.country'])->get();
    }

    public function registerUser($request)
    {
        $email = $request->input('email');

        //$user = User::where('email', 'like', $email)->first();
        $congress = Congress::where('congress_id', $request->input("congressId"));

        $newUser = new User();
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        if ($request->has('gender'))
            $newUser->gender = $request->input('gender');
        if ($request->has('mobile'))
            $newUser->establishment = $request->input('mobile');
        if ($request->has('city_id'))
            $newUser->city_id = $request->input('city_id');
        if ($request->has('country_id'))
            $newUser->city_id = $request->input('country_id');
        $newUser->email = $email;

        $newUser->email_verified = 0;
        $newUser->verification_code = str_random(40);
        $newUser->save();

        /* Generation QRcode */
        $qrcode = Utils::generateCode($newUser->id_User);
        $newUser->qr_code = $qrcode;
        $user = $newUser->save();

        $this->sendConfirmationMail($user, $congress->name);

        $this->settingInCongress($newUser, $request->input("congressId"));

        return $user;
    }

    public function sendConfirmationMail($user, $congress_name)
    {
        $link = "https://congress-api.vayetek.com/api/users/" . $user->id_User . "/validate/" . $user->validation_code;
        $email = $user->email;
        Mail::send('verifiactionMail', ['congress_name' => $congress_name, 'last_name' => $user->last_name,
            'first_name' => $user->first_name, 'link' => $link], function ($message) use ($email) {
            $message->to($email)->subject('Validation du compte');
        });
    }

    private function settingInCongress($user, $congressId)
    {
        $user_congress = new Congress_User();
        $user_congress->id_User = $user->id_User;
        $user_congress->id_Congress = $congressId;
        $user_congress->save();
        return $user_congress;
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

    public function getParticipatorByQrCode($qr_code)
    {
        return User::where('qr_code', '=', $qr_code)
            ->first();
    }

    public function makePresentToCongress($user, $isPresent)
    {
        if ($user->isPresent != 1 && $isPresent == 1) {
            $this->sendingToOrganisateur($user);
        }
        $user->isPresent = $isPresent;
        $user->update();

        return $user;
    }

    public function sendingToOrganisateur($participator)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            'http://137.74.165.25:3002/api/congress/users/send-present', [
                'form_params' => [
                    'user' => json_decode(json_encode($participator))
                ]
            ]);

        return json_decode($res->getBody(), true);
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

    public function addParticipant(Request $request, $congress_id)
    {
        $user = new User();
        $user->first_name = $request->input("first_name");
        $user->last_name = $request->input("last_name");

        if ($request->has('email'))
            $user->email = $request->input('email');
        if ($request->has('mobile'))
            $user->mobile = $request->input('mobile');

        if ($request->has('price'))
            $user->price = $request->input('price');

        $user->qr_code = str_random(7);
        $user->congress_id = $congress_id;
        $user->payement_type_id = $request->input('payement_type_id');

        $user->save();

        return $user;

    }

    public function affectAccess($user_id, $accessIds)
    {
        foreach ($accessIds as $accessId) {
            $user_access = new User_Access();
            $user_access->access_id = $accessId;
            $user_access->user_id = $user_id;
            $user_access->save();
        }
    }

    public function getUserById($user_id)
    {
        return User::with(["accesss"])
            ->where("user_id", "=", $user_id)
            ->first();
    }

    public function isAllowedAccess($participator, $accessId)
    {
        foreach ($participator->accesss as $access) {
            if ($access->access_id == $accessId) {
                return true;
            }
        }
        return false;
    }

    public function getAllPresencesByCongress($congressId)
    {
        return User::where("congress_id", "=", $congressId)
            ->where("isPresent", "=", 1)
            ->get();
    }

    public function generateQrCode($qr_code)
    {
    }

    public function getAllPayementTypes()
    {
        return Payement_Type::all();
    }

    public function getAllowedBadgeUsersByCongress($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('isBadgeGeted', '=', 0)
            ->get();
    }

    public function getUsersByCongress($congressId)
    {
        return User::where("congress_id", "=", $congressId)
            ->get();
    }

    public function getUsersByAccess($accessId)
    {
        return User::join('User_Access', 'User.user_id', '=', 'User_Access.user_id')
            ->where("access_id", '=', $accessId)
            ->get();

    }

    public function getPresencesByAccess($accessId)
    {
        return User::join('User_Access', 'User.user_id', '=', 'User_Access.user_id')
            ->where("access_id", '=', $accessId)
            ->where("User_Access.isPresent", "=", 1)
            ->get();
    }

    public function makePresentToAccess($user, $accessId, $isPresent)
    {
        $user_access = $this->getUserAccessByUser($user->user_id, $accessId);

        if ($user_access->isPresent != 1 && $isPresent == 1) {
            $this->sendingRTAccess($user, $accessId);
        }
        $user_access->isPresent = $isPresent;
        $user_access->update();
    }

    public function getUserAccessByUser($userId, $accessId)
    {
        return User_Access::where("user_id", "=", $userId)
            ->where("access_id", "=", $accessId)
            ->first();
    }

    private function sendingRTAccess($user, $accessId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            'http://137.74.165.25:3002/api/congress/users/send-present-access', [
                'form_params' => [
                    'user' => json_decode(json_encode($user)),
                    'accessId' => $accessId
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

    private function isExistCongress($user, $congressId)
    {
        return Congress_User::where("id_User", "=", $user->id_User)
            ->where("id_Congress", "=", $congressId)->first();
    }
}