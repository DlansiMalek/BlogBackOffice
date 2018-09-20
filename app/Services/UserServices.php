<?php

namespace App\Services;

use App\Models\Access_Presence;
use App\Models\Admin;
use App\Models\Payement_Type;
use App\Models\User;
use App\Models\User_Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PDF;

class UserServices
{

    public function getAllUsers()
    {
        return User::with(['city', 'city.country'])->get();
    }

    public function registerUser(Request $request)
    {
        $email = $request->input('email');

        //$user = User::where('email', 'like', $email)->first();
        // $congress = Congress::where('congress_id', $request->input("congressId"))->first();

        $newUser = new User();
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        if ($request->has('sex'))
            $newUser->gender = $request->input('sex');
        if ($request->has('mobile'))
            $newUser->mobile = $request->input('mobile');
        if ($request->has('city_id'))
            $newUser->city_id = $request->input('city_id');
        if ($request->has('country_id'))
            $newUser->city_id = $request->input('country_id');

        if ($request->has('grade_id'))
            $newUser->grade_id = $request->input('grade_id');

        if ($request->has('lieu_ex_id'))
            $newUser->lieu_ex_id = $request->input('lieu_ex_id');

        $newUser->email = $email;

        $newUser->email_verified = 0;
        $newUser->verification_code = str_random(40);

        /* Generation QRcode */
        $qrcode = Utils::generateCode($newUser->user_id);
        $newUser->qr_code = $qrcode;

        $newUser->congress_id = $request->input("congressId");

        $newUser->save();

        // $this->sendConfirmationMail($newUser, $congress->name);

        return $newUser;
    }

    public function sendConfirmationMail($user, $congress_name)
    {
        $link = "https://congress-api.vayetek.com/api/users/" . $user->user_id . "/validate/" . $user->verification_code;
        $email = $user->email;
        Mail::send('verificationMail', ['congress_name' => $congress_name, 'last_name' => $user->last_name,
            'first_name' => $user->first_name, 'link' => $link], function ($message) use ($email) {
            $message->to($email)->subject('Validation du compte');
        });
    }

    public function getParticipatorById($user_id)
    {
        return User::with(['accesss'])->where('user_id', '=', $user_id)
            ->first();
    }

    public function updateUser($request, $updateUser)
    {
        if (!$updateUser) {
            return null;
        }
        $updateUser->first_name = $request->input('first_name');
        $updateUser->last_name = $request->input('last_name');
        $updateUser->gender = $request->input('gender');
        $updateUser->mobile = $request->input('mobile');
        $updateUser->city_id = $request->input('city_id');
        $updateUser->country_id = $request->input('country_id');
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
        $pathToFile = storage_path() . "/app/badge.png";


        try {
            Mail::send('inscriptionEmail.' . $congress->congress_id, ['accesss' => $user->accesss
            ], function ($message) use ($email, $congress, $pathToFile) {
                $message->attach($pathToFile);
                $message->to($email)->subject($congress->object_mail_inscription);
            });
        } catch (\Exception $exception) {
            Log::info($exception);
            $user->email_sended = -1;
            $user->update();
            return 1;
        }

        $user->email_sended = 1;
        $user->update();
        return 1;
    }

    public function sendCredentialsOrganizerMail(Admin $admin)
    {
        $email = $admin->email;
        $pathToFile = storage_path() . "/app/badge.png";


        try {
            Mail::send('emailCredentialsOrganizer', ['email' => $email, 'password' => $admin->passwordDecrypt
            ], function ($message) use ($email, $pathToFile) {
                // $message->attach($pathToFile);
                $message->to($email)->subject('Vos identifiants pour VayeCongress');
            });
        } catch (\Exception $exception) {
            Log::info($exception);
            return 1;
        }
        return 1;
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
        return User::with(['accesss.attestation'])
            ->where('qr_code', '=', $qr_code)
            ->first();
    }

    public function makePresentToCongress($user, $isPresent)
    {
        if ($user->isPresent != 1 && $isPresent == 1) {
            $this->sendingToOrganisateur($user);

            $userAccesses = $user->accesss;
            foreach ($userAccesses as $userAccess) {
                $userAccess->total_present_in_congress++;
                $userAccess->update();
            }
        }
        $user->isPresent = $isPresent;
        $user->update();

        return $user;
    }

    public function sendingToOrganisateur($participator)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            Utils::$baseUrlRT . "/congress/users/send-present", [
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
            $query->where("Congress_User . id_Congress", "=", $congressId)
                ->where("Congress_User . isPresent", "=", 1);
        }])->
        withCount(['congresses as isPaid' => function ($query) use ($congressId) {
            $query->where("Congress_User . id_Congress", "=", $congressId)
                ->where("Congress_User . isPaid", "=", 1);;
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

        if ($request->has('type'))
            $user->mobile = $request->input('type');

        if ($request->has('price'))
            $user->price = $request->input('price');

        $user->qr_code = str_random(7);
        $user->congress_id = $congress_id;
        $user->payement_type_id = $request->input('payement_type_id');

        $user->save();

        return $user;

    }

    public function affectAccessIds($user_id, $accessIds)
    {
        foreach ($accessIds as $item) {
            $this->affectAccessById($user_id, $item);
        }
    }

    public function affectAccess($user_id, $accessIds)
    {
        $access1 = 0;
        $access2 = 0;
        for ($i = 0; $i < sizeof($accessIds); $i++) {
            if ($accessIds[$i] == 2 || $accessIds[$i] == 3 || $accessIds[$i] == 4) {
                if ($access1 != 0) {
                    continue;
                }
                $access1 = 1;

            }
            if ($accessIds[$i] == 5 || $accessIds[$i] == 6 || $accessIds[$i] == 7) {
                if ($access2 != 0) {
                    continue;
                }
                $access2 = 1;
            }
            $this->affectAccessById($user_id, $accessIds[$i]);

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
        return User::with(['accesss.attestation'])
            ->where("congress_id", "=", $congressId)
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

    public function makePresentToAccess($user_access, $user, $accessId, $isPresent, $type)
    {

        if ($user_access->isPresent != 1 && $isPresent == 1) {
            $this->sendingRTAccess($user, $accessId);
        }

        if ($user_access->isPresent == 0) {
            if ($type == 1) {
                //Enter
                $this->removeAllPresencePerAccess($accessId, $user->user_id);
                $this->addingNewEnter($user->user_id, $accessId);
            }
        } else {
            if ($type == 1) {
                //Enter
                if (!$presence_access = $this->getLastEnterUser($user->user_id, $accessId)) {
                    $this->addingNewEnter($user->user_id, $accessId);
                }
            } else {
                //Leave
                if ($presence_access = $this->getLastEnterUser($user->user_id, $accessId)) {
                    $presence_access->leave_time = date('Y-m-d H:i:s');
                    $presence_access->update();
                }
            }

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
            Utils::$baseUrlRT . '/congress/users/send-present-access', [
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
            Utils::$baseUrlRT . '/congress/users/send-all', [
                'form_params' => [
                    'users' => json_decode(json_encode($allParticipants)),
                    'congressId' => $congressId
                ]
            ]);

        return json_decode($res->getBody(), true);
    }

    public function getUserByEmail($congressId, $email)
    {
        return User::where('email', '=', $email)
            ->where('congress_id', '=', $congressId)
            ->first();
    }

    public function getUsersByCongressWithAccess($congressId)
    {
        return User::with(['accesss'])
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getUsersEmailNotSendedByCongress($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('email_sended', '=', 0)
            ->get();
    }

    public function addFastUser(Request $request)
    {
        $newUser = new User();
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        $newUser->lieu_ex_id = $request->input('lieu_ex_id');
        $newUser->grade_id = $request->input('grade_id');
        $newUser->gender = $request->input("gender");

        if ($request->has('email') && $request->input('email') != "")
            $newUser->email = $request->input('email');
        if ($request->has("mobile"))
            $newUser->mobile = $request->input("mobile");
        /* Generation QRcode */
        $qrcode = Utils::generateCode($newUser->user_id);
        $newUser->qr_code = $qrcode;
        $newUser->congress_id = $request->input("congressId");
        $newUser->save();
        return $newUser;
    }

    public function editFastUser($newUser, Request $request)
    {
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        $newUser->lieu_ex_id = $request->input('lieu_ex_id');
        $newUser->grade_id = $request->input('grade_id');
        $newUser->gender = $request->input("gender");

        if ($request->has('email') && $request->input('email') != "")
            $newUser->email = $request->input('email');
        if ($request->has("mobile"))
            $newUser->mobile = $request->input("mobile");

        $newUser->update();
        return $newUser;
    }

    public function updateAccess($user_id, array $accessDiff, array $userAccessIds)
    {
        foreach ($accessDiff as $item) {
            if (in_array($item, $userAccessIds)) {
                $this->deleteAccessById($user_id, $item);
            } else {
                $this->affectAccessById($user_id, $item);
            }
        }

    }

    public function deleteAccess($user_id, array $accessDiffDeleted)
    {
        foreach ($accessDiffDeleted as $item) {
            $this->deleteAccessById($user_id, $item);
        }
    }


    private function isExistCongress($user, $congressId)
    {
        return Congress_User::where("id_User", "=", $user->id_User)
            ->where("id_Congress", "=", $congressId)->first();
    }

    private function removeAllPresencePerAccess($accessId, $user_id)
    {
        return Access_Presence::where('user_id', '=', $user_id)
            ->where('access_id', '=', $accessId)
            ->delete();
    }

    private function addingNewEnter($user_id, $accessId)
    {
        $access_presence = new Access_Presence();
        $access_presence->user_id = $user_id;
        $access_presence->access_id = $accessId;
        $access_presence->enter_time = date('Y-m-d H:i:s');
        $access_presence->save();
    }

    private function getLastEnterUser($user_id, $accessId)
    {
        return Access_Presence::whereNull('leave_time')
            ->where('user_id', '=', $user_id)
            ->where('access_id', '=', $accessId)
            ->first();
    }

    private function affectAccessById($user_id, $accessId)
    {
        $user_access = new User_Access();
        $user_access->access_id = $accessId;
        $user_access->user_id = $user_id;
        $user_access->save();
    }

    private function deleteAccessById($user_id, $accessId)
    {
        return User_Access::where('user_id', '=', $user_id)
            ->where('access_id', '=', $accessId)
            ->delete();
    }
}