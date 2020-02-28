<?php

namespace App\Services;

use App\Models\AccessPresence;
use App\Models\Admin;
use App\Models\AttestationRequest;
use App\Models\FormInputResponse;
use App\Models\Payment;
use App\Models\ResponseValue;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\UserCongress;
use App\Models\UserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PDF;

class UserServices
{

    private $path = 'profile-pic/';

    public function getAllUsers()
    {
        return User::orderBy('updated_at', 'asc')
            ->get();
    }

    public function updateUserPathCV($path, $user)
    {
        if (!$path)
            return null;
        $user->path_cv = $path;
        $user->update();
        return $user;
    }

    public function makeUserPathCvNull($user)
    {
        $user->path_cv = null;
        $user->update();
        return $user;
    }

    public function editerUser(Request $request, $newUser)
    {
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        if ($request->has('gender'))
            $newUser->gender = $request->input('gender');
        if ($request->has('mobile'))
            $newUser->mobile = $request->input('mobile');
        if ($request->has('country_id'))
            $newUser->country_id = $request->input('country_id');

        if ($request->has('pack_id'))
            $newUser->pack_id = $request->input('pack_id');

        if ($request->has('organization_id'))
            $newUser->organization_id = $request->input('organization_id');
        else $newUser->organization_id = null;

        if ($request->has('privilege_id')) {
            $newUser->privilege_id = $request->input('privilege_id');
        }
        $newUser->update();
        return $newUser;
    }

    public function registerUser(Request $request)
    {
        $email = $request->input('email');

        //$user = User::where('email', 'like', $email)->first();
        // $congress = Congress::where('congress_id', $request->input("congressId"))->first();
        $newUser = new User();
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');

        if ($request->has('pack_id'))
            $newUser->pack_id = $request->input('pack_id');

        if ($request->has('gender'))
            $newUser->gender = $request->input('gender');
        if ($request->has('mobile'))
            $newUser->mobile = $request->input('mobile');
        if ($request->has('country_id'))
            $newUser->country_id = $request->input('country_id');

        if ($request->has('price'))
            $newUser->price = $request->input('price');

        if ($request->has('organization_id') && $request->input('organization_id'))
            $newUser->organization_id = $request->input('organization_id');

        if ($request->has('free'))
            $newUser->free = $request->input('free');

        if ($request->has('organization_accepted') && $request->get('organization_accepted') == true) {
            $newUser->organization_accepted = $request->input('organization_accepted');
            $newUser->isPaid = true;
        }

        if ($request->has('privilege_id')) {
            $newUser->privilege_id = $request->input('privilege_id');
        } else {
            $newUser->privilege_id = 3;
        }

        $newUser->email = $email;

        $newUser->email_verified = 0;
        $newUser->verification_code = str_random(40);

        /* Generation QRcode */
        $qrcode = Utils::generateCode($newUser->user_id);
        $newUser->qr_code = $qrcode;

        $newUser->congress_id = $request->input("congressId");

        $newUser->save();
        return $this->getUserById($newUser->user_id);
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
        $user = User::with(['payments', 'accesses', 'responses.values', 'responses.form_input.values',
            'responses.form_input.type'])->where('user_id', '=', $user_id)
            ->first();
        return $user;
    }

    public function updateUser(Request $request, $updateUser)
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


    public function sendCredentialsOrganizerMail(Admin $admin)
    {
        $email = $admin->email;
        $pathToFile = storage_path() . "/app/badge.png";


        try {
            Mail::send('emailCredentialsOrganizer', ['email' => $email, 'password' => $admin->passwordDecrypt
            ], function ($message) use ($email, $pathToFile) {
                $message->attach($pathToFile);
                $message->to($email)->subject('Accès à la plateforme Eventizer');
            });
        } catch (\Exception $exception) {
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

    public function getParticipatorByQrCode($qr_code, $congressId)
    {
        return User::where('qr_code', '=', $qr_code)->with(['accesses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])
            ->first();
    }

    public function makePresentToCongress($user, $isPresent)
    {
        if ($user->isPresent != 1 && $isPresent == 1) {
            // $this->sendingToOrganisateur($user);

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
            UrlUtils::getUrlRT() . "/congress/users/send-present", [
                'form_params' => [
                    'user' => json_decode(json_encode($participator))
                ]
            ]);

        return json_decode($res->getBody(), true);
    }

    public function getParticipatorByIdByCongress($userId, $congressId)
    {
        return User::withCount(['congresses as isPresent' => function ($query) use ($congressId) {
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

        if ($request->has('privilege_id')) {
            $user->privilege_id = $request->input('privilege_id');
        } else {
            $user->privilege_id = 3;
        }


        $user->qr_code = str_random(7);
        $user->congress_id = $congress_id;
        $user->payement_type_id = $request->input('payement_type_id');

        $user->save();

        return $this->getUserById($congress_id);

    }

    public function affectAccessIds($user_id, $accessIds)
    {
        foreach ($accessIds as $item) {
            $this->affectAccessById($user_id, $item);
        }
    }

    public function affectAccess($user_id, $accessIds, $packAccesses)
    {
        for ($i = 0; $i < sizeof($accessIds); $i++) {
            $this->affectAccessById($user_id, $accessIds[$i]);
        }

        foreach ($packAccesses as $access) {
            if (!in_array($access->access_id, $accessIds)) {
                $this->affectAccessById($user_id, $access->access_id);
            }
        }
    }

    public function getUserIdAndByCongressId($userId, $congressId, $showInRegister)
    {
        return User::with(["accesses" => function ($query) use ($congressId, $showInRegister) {
            $query->where('congress_id', '=', $congressId);
            $query->where('show_in_register', '=', $showInRegister);
        }])
            ->where("user_id", "=", $userId)
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
        return User::whereHas('user_congresses', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId)
                ->where("isPresent", "=", 1);
        })->get();
    }


    public function getAllowedBadgeUsersByCongress($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('isBadgeGeted', '=', 0)
            ->get();
    }

    public function getUsersMinByCongress($congressId, $privilegeId)
    {
        return User::whereHas('user_congresses', function ($query) use ($congressId, $privilegeId) {
            $query->where('congress_id', '=', $congressId);
            if ($privilegeId != null) {
                $query->where('privilege_id', '=', $privilegeId);
            }
        })->get();
    }

    public function getUsersByCongress($congressId, $privilegeIds = null, $withAttestation = null, $perPage = null, $search = null, $tri = null, $order = null)
    {
        $users = User::whereHas('user_congresses', function ($query) use ($congressId, $privilegeIds) {
            $query->where('congress_id', '=', $congressId);
            if ($privilegeIds != null) {
                $query->whereIn('privilege_id', $privilegeIds);
            }
        })  
            ->with(['user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'accesses' => function ($query) use ($congressId, $withAttestation) {
                $query->where('congress_id', '=', $congressId);
                if ($withAttestation != null) {
                    $query->where("with_attestation", "=", $withAttestation);
                }
            }, 'accesses.attestations', 'organization', 'user_congresses.privilege', 'country', 'payments' => function ($query) use ($congressId, $tri, $order) {
                $query->where('congress_id', '=', $congressId);
                if ($tri == 'isPaid')
                    $query->orderBy($tri, $order);
            }])
            ->where(function ($query) use ($search) {
                if ($search != "") {
                    $query->whereRaw('lower(first_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(last_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(email) like (?)', ["%{$search}%"]);
                }
            });
        //->orderBy($tri, $order);
        if ($order && ($tri == 'user_id' || $tri == 'country_id')) {
            $users = $users->orderBy($tri, $order);
        }
        if ($order && $tri == 'isPaid') {
            $users = $users->join('Payment', 'Payment.user_id', '=', 'User.user_id')
                ->where('Payment.congress_id', '=', $congressId)
                ->orderBy($tri, $order)
                ->orderBy('');
        }

        return $perPage ? $users->paginate($perPage) : $users->get();
    }

    public function getAllUsersByCongress($congressId){
        $users=User::whereHas('user_congresses', function($query) use ($congressId){
            $query->where('congress_id', '=', $congressId);
        })
        ->with(['user_congresses'=> function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        },'payments'=> function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        },'responses'=>function ($query) use ($congressId){
            $query->where('congress_id','=',$congressId);
        },'country'])
        ->get();
        return $users;
    }

    public function getUsersByAccess($congressId, $accessId)
    {
        return User::whereHas('accesses', function ($query) use ($accessId) {
            $query->where('Access.access_id', '=', $accessId);
        })
            ->with(['country',
                'payments' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                },
                'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }, 'user_congresses.privilege'])
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

        /*if ($user_access->isPresent != 1 && $isPresent == 1) {
            $this->sendingRTAccess($user, $accessId);
        }*/

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
                    $presence_access->left_at = date('Y-m-d H:i:s');
                    $presence_access->update();
                }
            }

        }
        $user_access->isPresent = $isPresent;
        $user_access->update();
    }

    public function getUserAccessByUser($userId, $accessId)
    {
        return UserAccess::where("user_id", "=", $userId)
            ->where("access_id", "=", $accessId)
            ->first();
    }

    public function updateQrCode($user_id, string $generateCode)
    {
        User::where('user_id', '=', $user_id)
            ->update(['qr_code' => $generateCode]);
    }

    public function getUserModifiedDate($date)
    {
        return User::where("updated_at", ">=", date('Y-m-d'))
            ->get();
    }

    public function getUsersWithRelations($congressId, $relations, $isPresent)
    {
        return User::whereHas('user_congresses', function ($query) use ($congressId, $isPresent) {
            $query->where('congress_id', '=', $congressId);
            if ($isPresent != null)
                $query->where('isPresent', '=', $isPresent);
        })
            ->with($relations)
            ->get();
    }

    public function getUserByEmailAndCode($email, $code)
    {
        return User::with(['user_congresses.congress.accesss.speakers',
            'user_congresses.congress.accesss.chairs',
            'user_congresses.congress.accesss.sub_accesses',
            'user_congresses.congress.accesss.topic',
            'user_congresses.congress.accesss.type',
            'user_congresses.privilege',
            'user_congresses.pack',
            'accesses',
            'speaker_access',
            'chair_access',
            'country',
            'likes'])
            ->whereRaw('lower(email) like (?)', ["{$email}"])
            ->where('code', '=', $code)
            ->first();
    }

    private function sendingRTAccess($user, $accessId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request('POST',
            UrlUtils::getUrlRT() . '/congress/users/send-present-access', [
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
            UrlUtils::getUrlRT() . '/congress/users/send-all', [
                'form_params' => [
                    'users' => json_decode(json_encode($allParticipants)),
                    'congressId' => $congressId
                ]
            ]);

        return json_decode($res->getBody(), true);
    }

    public function getUserByEmail($email)
    {
        $email = strtolower($email);
        return User::whereRaw('lower(email) like (?)', ["{$email}"])
            ->first();
    }


    public function getUsersByEmail($email)
    {
        $users = User::with(['congress.accesss.quiz_associations.scores', 'accesss', 'congress.feedback_questions.type', 'congress.feedback_questions.values', 'feedback_responses'])
            ->where('email', '=', $email)
            ->get();
        foreach ($users as $user) {
            $admin = Admin::find($user->congress->admin_id);
            $user->adminPhone = $admin->mobile ? $admin->mobile : 0;
            $user->adminEmail = $admin->email;
            $user->voting_token = $admin->voting_token;
        }
        return $users;
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
        $newUser->gender = $request->input("gender");

        if ($request->has('country_id'))
            $newUser->country_id = $request->input('country_id');

        if ($request->has('price'))
            $newUser->price = $request->input('price');


        if ($request->has('organization_id') && $request->input('organization_id') != 0) {
            $newUser->organization_id = $request->input('organization_id');
        }
        if ($request->has('pack_id') && $request->input('pack_id') != 0) {
            $newUser->pack_id = $request->input('pack_id');
        }
        if ($request->has('privilege_id')) {
            $newUser->privilege_id = $request->input('privilege_id');
        } else {
            $newUser->privilege_id = 3;
        }

        if ($request->has('email') && $request->input('email') != "")
            $newUser->email = $request->input('email');
        if ($request->has("mobile"))
            $newUser->mobile = $request->input("mobile");
        /* Generation QRcode */
        $qrcode = Utils::generateCode($newUser->user_id);
        $newUser->qr_code = $qrcode;
        $newUser->congress_id = $request->input("congressId");
        $newUser->save();
        return $this->getUserById($newUser->user_id);
    }

    public function editFastUser($newUser, Request $request)
    {
        $newUser->first_name = $request->input('first_name');
        $newUser->last_name = $request->input('last_name');
        $newUser->gender = $request->input("gender");

        if ($request->has('country_id'))
            $newUser->country_id = $request->input('country_id');

        if ($request->has('price'))
            $newUser->price = $request->input('price');
        else
            $newUser->price = null;

        if ($request->has('organization_id') && $request->input('organization_id') != 0) {
            $newUser->organization_id = $request->input('organization_id');
        } else
            $newUser->organization_id = null;
        if ($request->has('pack_id') && $request->input('pack_id') != 0) {
            $newUser->pack_id = $request->input('pack_id');
        } else
            $newUser->pack_id = null;

        if ($request->has('email') && $request->input('email') != "")
            $newUser->email = $request->input('email');
        if ($request->has("mobile"))
            $newUser->mobile = $request->input("mobile");

        if ($request->has('privilege_id')) {
            $newUser->privilege_id = $request->input('privilege_id');
        } else {
            $newUser->privilege_id = 3;
        }

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

    public function getUsersByCongressByPrivileges($congressId, $privileges)
    {
        return User::whereIn('privilege_id', $privileges)
            ->where("congress_id", "=", $congressId)
            ->with(['accesss.attestations', 'organization', 'privilege'])
            ->get();
    }

    public function saveUsersFromExcel($congress_id, $users)
    {
        foreach ($users as $user) {
            $this->addUserExcel($congress_id, $user);
        }
    }

    public function getFastUsersByCongressId($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('privilege_id', '=', 3)
            ->get();
    }

    public function sendMail($view, $user, $congress, $objectMail, $fileAttached, $userMail = null)
    {

        //TODO detect email sended user
        $email = $user->email;
        $pathToFile = storage_path() . "/app/badge.png";

        if ($congress->username_mail)
            config(['mail.from.name', $congress->username_mail]);

        try {
            Mail::send([], [], function ($message) use ($email, $congress, $pathToFile, $fileAttached, $objectMail, $view) {
                $fromMailName = $congress->config && $congress->config->from_mail ? $congress->config->from_mail : env('MAIL_FROM_NAME', 'Eventizer');

                if ($congress->config && $congress->config->replyto_mail) {
                    $message->replyTo($congress->config->replyto_mail);
                }

                $message->from(env('MAIL_USERNAME', 'contact@eventizer.io'), $fromMailName);
                $message->subject($objectMail);
                $message->setBody($view, 'text/html');
                if ($fileAttached)
                    $message->attach($pathToFile);
                $message->to($email)->subject($objectMail);
            });
        } catch (\Exception $exception) {
            return $exception;
            if ($userMail) {
                $userMail->status = -1;
                $userMail->update();
            }
            Storage::delete('/app/badge.png');
            return 1;
        }
        if ($userMail) {
            $userMail->status = 1;
            $userMail->update();
        }
        Storage::delete('/app/badge.png');
        return 1;
    }


    public function sendMailAttesationToUser($user, $congress, $userMail, $object, $view)
    {
        $email = $user->email;

        $pathToFile = storage_path() . "/app/attestations.zip";

        try {
            Mail::send([], [], function ($message) use ($view, $object, $email, $congress, $pathToFile) {
                $fromMailName = $congress->config && $congress->config->from_mail ? $congress->config->from_mail : env('MAIL_FROM_NAME', 'Eventizer');

                if ($congress->config && $congress->config->replyto_mail) {
                    $message->replyTo($congress->config->replyto_mail);
                }

                $message->from(env('MAIL_USERNAME', 'contact@eventizer.io'), $fromMailName);
                $message->subject($object);
                $message->setBody($view, 'text/html');
                $message->attach($pathToFile);
                $message->to($email)->subject($object);
            });
            $userMail->status = 1;
        } catch (\Exception $exception) {
            Storage::delete('app/badge.png');
            $userMail->status = -1;
        }
        $userMail->update();
        return $user;
    }

    public function getUsersEmailAttestationNotSendedByCongress($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('email_attestation_sended', '=', 0)
            ->get();
    }

    public function uploadPayement($userPayment, Request $request)
    {
        ini_set('post_max_size', '15M');
        ini_set('upload_max_filesize', '15M');

        $file = $request->file('file_data');
        $chemin = config('media.payement-user-recu');
        $path = $file->store($chemin);

        $userPayment->path = $path;
        $userPayment->isPaid = 2;

        $userPayment->update();

        return $userPayment;
    }

    public function getUserByRef($ref)
    {
        return User::where('ref_payment', '=', $ref)
            ->first();
    }

    public function getUsersByContry($congressId, $countryId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('country_id', '=', $countryId)
            ->get();
    }

    public function saveUserResponses($responses, $userId)
    {
        foreach ($responses ? $responses : [] as $req) {

            $reponse = new FormInputResponse();
            if (!array_key_exists("response", $req)) {

                $reponse->user_id = $userId;
                $reponse->form_input_id = $req['form_input_id'];
                $reponse->response = null;
                $reponse->save();

                continue;
            } else {
                if (in_array($req['type']['name'], ['checklist', 'radio', 'select', 'multiselect']))
                    $reponse->response = "";
                else $reponse->response = $req['response'];
            }

            $reponse->user_id = $userId;
            $reponse->form_input_id = $req['form_input_id'];
            $reponse->save();
            if (in_array($req['type']['name'], ['checklist', 'multiselect']))
                foreach ($req['response'] as $val) {
                    $repVal = new ResponseValue();
                    $repVal->form_input_response_id = $reponse->form_input_response_id;
                    $repVal->form_input_value_id = $val;
                    if (!$val)
                        continue;

                    $repVal->save();
                }
            else if (in_array($req['type']['name'], ['radio', 'select'])) {
                $repVal = new ResponseValue();
                $repVal->form_input_response_id = $reponse->form_input_response_id;
                $repVal->form_input_value_id = $req['response'];
                if (!$req['response'])
                    continue;
                $repVal->save();
            }

        }
    }

    public function deleteUserResponses($user_id)
    {
        $responses = FormInputResponse::with('values')->where('user_id', '=', $user_id)->get();
        foreach ($responses as $resp) {
            ResponseValue::where('form_input_response_id', '=', $resp->form_input_response_id)->delete();
            $resp->delete();
        }
    }

    public function isRegisteredToAccess($user_id, $access_id)
    {
        return count(UserAccess::where("user_id", "=", $user_id)->where("access_id", '=', $access_id)->get()) > 0;
    }

    public function usedQrCode($qr)
    {
        $users = User::where("qr_code", '=', $qr)->get();
        return count($users) > 0;
    }

    public function getUsersByParticipantTypeId($congressId, $participantTypeId)
    {
        return User::where('privilege_id', '=', $participantTypeId)
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function affectAllAccess($user_id, $accesss)
    {
        foreach ($accesss as $access) {
            $userAccess = new UserAccess();
            $userAccess->user_id = $user_id;
            $userAccess->access_id = $access->access_id;
            $userAccess->save();
        }
    }

    public function getUserByTypeAndCongressId($congress_id, $privilegeIds)
    {
        return User::with('user_congresses.congress', 'user_congresses.privilege')
            ->whereHas('user_congresses', function ($query) use ($congress_id, $privilegeIds) {
                $query->where('congress_id', '=', $congress_id)->whereIn('privilege_id', $privilegeIds);
            })->get();
    }

    public function addUser($userData)
    {
        $user = new User();

        $user->email = $userData['email'];
        $user->first_name = $userData['first_name'];
        $user->last_name = $userData['last_name'];
        $user->gender = $userData['gender'];
        $user->mobile = $userData['mobile'];
        $user->country_id = $userData['country_id'];

        $user->save();

        if (!$user->qr_code) {
            $user->qr_code = Utils::generateCode($user->user_id);
            $user->update();
        }

        return $user;
    }

    public function saveUser(Request $request)
    {
        $user = new User();
        $user->email = $request->email;
        if ($request->has('first_name')) $user->first_name = $request->input('first_name');
        if ($request->has('last_name')) $user->last_name = $request->input('last_name');
        if ($request->has('gender')) $user->gender = $request->input('gender');
        if ($request->has('mobile')) $user->mobile = $request->input('mobile');
        if ($request->has('code')) $user->code = $request->input('code');
        if ($request->has('country_id')) $user->country_id = $request->country_id;
        $user->verification_code = str_random(40);
        $user->save();
        if (!$user->qr_code) {
            $user->qr_code = Utils::generateCode($user->user_id);
            $user->update();
        }

        return $user;
    }

    public function editUser(Request $request, $user)
    {
        $user->email = $request->email;
        if ($request->has('first_name')) $user->first_name = $request->input('first_name');
        if ($request->has('last_name')) $user->last_name = $request->input('last_name');
        if ($request->has('gender')) $user->gender = $request->input('gender');
        if ($request->has('mobile')) $user->mobile = $request->input('mobile');
        if ($request->has('code')) $user->code = $request->input('code');
        if ($request->has('country_id')) $user->country_id = $request->country_id;

        $user->update();
        return $user;
    }

    public function getUserCongress($congress_id, $user_id)
    {
        return UserCongress::where('user_id', '=', $user_id)->where('congress_id', '=', $congress_id)->first();
    }

    public function getUserCongressByUserId($userId)
    {
        return UserCongress::where('user_id', '=', $userId)
            ->get();
    }

    public function saveUserCongress($congress_id, $user_id, Request $request)
    {
        $user_congress = new UserCongress();
        $user_congress->user_id = $user_id;
        $user_congress->congress_id = $congress_id;
        $user_congress->privilege_id = $request->privilege_id;

        if ($request->has('organization_id'))
            $user_congress->organization_id = $request->input('organization_id');
        if ($request->has('pack_id'))
            $user_congress->pack_id = $request->input("pack_id");

        $user_congress->save();
        return $user_congress;
    }

    public function deleteUserAccesses($user_id, $congress_id)
    {
        UserAccess::with('access')->whereHas('access', function ($query) use ($congress_id) {
            $query->where('congress_id', '=', $congress_id);
        })
            ->where('user_id', '=', $user_id)
            ->delete();
    }

    public function getMinUserByQrCode($qrCode)
    {
        return User::where("qr_code", "=", $qrCode)
            ->get();
    }

    public function getUserByQrCode($qrCode)
    {
        return User::with(['user_congresses.congress.accesss.speakers',
            'user_congresses.congress.accesss.chairs',
            'user_congresses.congress.accesss.sub_accesses',
            'user_congresses.congress.accesss.topic',
            'user_congresses.congress.accesss.type',
            'user_congresses.privilege',
            'user_congresses.pack',
            'accesses',
            'speaker_access',
            'chair_access',
            'country',
            'likes'])
            ->where('qr_code', '=', $qrCode)
            ->first();
    }


    public function getUserByIdWithRelations($userId, $relations)
    {
        return User::with($relations)
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function getUserById($userId)
    {
        return User::with(['user_congresses.congress.accesss.speakers',
            'user_congresses.congress.accesss.chairs',
            'user_congresses.congress.accesss.sub_accesses',
            'user_congresses.congress.accesss.topic',
            'user_congresses.congress.accesss.type',
            'user_congresses.privilege',
            'user_congresses.pack',
            'accesses',
            'speaker_access',
            'chair_access',
            'country',
            'likes'])
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function getUserCongressLocal($userCongresss, $congressId)
    {
        return Utils::objArraySearch($userCongresss, 'congress_id', $congressId);

    }

    public function getPaymentInfoByUserAndCongress($userId, $congressId)
    {
        return Payment::where('congress_id', '=', $congressId)
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function getPaymentByUserId($congressId, $userId)
    {
        return Payment::where('congress_id', '=', $congressId)
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function getPaymentById($paymentId)
    {
        return Payment::where('payment_id', '=', $paymentId)
            ->first();
    }

    public function affectAccessElement($user_id, $accesses)
    {
        foreach ($accesses as $access) {
            $this->affectAccessById($user_id, $access->access_id);
        }
    }

    public function updateUserIdUserCongress($oldUserId, $newUserId)
    {
        UserCongress::where('user_id', '=', $oldUserId)
            ->update(['user_id' => $newUserId]);

        Payment::where('user_id', '=', $oldUserId)
            ->update(['user_id' => $newUserId]);

        UserAccess::where('user_id', '=', $oldUserId)
            ->update(['user_id' => $newUserId]);

        UserMail::where('user_id', '=', $oldUserId)
            ->update(['user_id' => $newUserId]);

        FormInputResponse::where('user_id', '=', $oldUserId)
            ->update(['user_id' => $newUserId]);


    }

    public function deleteById($user_id)
    {
        return User::where('user_id', '=', $user_id)
            ->delete();
    }

    public function deleteFormInputUser($userId, $congressId)
    {
        return FormInputResponse::whereHas('form_input', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        })
            ->where("user_id", '=', $userId)
            ->delete();
    }

    public function updateUserCongress($userCongress, Request $request)
    {
        $userCongress->privilege_id = $request->input("privilege_id");
        if ($request->has('organization_id'))
            $userCongress->organization_id = $request->input('organization_id');
        if ($request->has('pack_id'))
            $userCongress->pack_id = $request->input("pack_id");

        $userCongress->update();
    }

    public function affectAccessToUsers($access, $users)
    {
        foreach ($users as $user) {
            $this->affectAccessById($user->user_id, $access->access_id);
        }

    }


    private function isExistCongress($user, $congressId)
    {
        return Congress_User::where("id_User", "=", $user->id_User)
            ->where("id_Congress", "=", $congressId)->first();
    }

    private function removeAllPresencePerAccess($accessId, $user_id)
    {
        return AccessPresence::where('user_id', '=', $user_id)
            ->where('access_id', '=', $accessId)
            ->delete();
    }

    private function addingNewEnter($user_id, $accessId)
    {
        $access_presence = new AccessPresence();
        $access_presence->user_id = $user_id;
        $access_presence->access_id = $accessId;
        $access_presence->entered_at = date('Y-m-d H:i:s');
        $access_presence->save();
    }

    private function getLastEnterUser($user_id, $accessId)
    {
        return AccessPresence::whereNull('left_at')
            ->where('user_id', '=', $user_id)
            ->where('access_id', '=', $accessId)
            ->first();
    }

    public function affectAccessById($user_id, $accessId)
    {
        $user_access = new UserAccess();
        $user_access->access_id = $accessId;
        $user_access->user_id = $user_id;
        $user_access->save();

        return $user_access;
    }

    private function deleteAccessById($user_id, $accessId)
    {
        return UserAccess::where('user_id', '=', $user_id)
            ->where('access_id', '=', $accessId)
            ->delete();
    }

    public function getUserByRfid($rfid)
    {
        return User::where('rfid', '=', $rfid)->with(['accesss.attestation'])->first();
    }

    private function addUserExcel($congress_id, $user)
    {
        $userData = new User();

        $userData->first_name = $user['first_name'];
        $userData->last_name = $user['last_name'];
        $userData->gender = 1; // TODO dynamic
        if (array_key_exists('mobile', $user))
            $userData->mobile = $user['mobile'];
        if (array_key_exists('email', $user) && $user['email'] != "") {
            $userData->email = $user['email'];
        }

        if (array_key_exists('country_id', $user)) {
            $userData->country_id = $user['country_id'];
        }
        if (array_key_exists('organization_id', $user) && $user['organization_id'] != 0) {
            $userData->organization_id = $user['organization_id'];
        }
        $userData->congress_id = $congress_id;
        if (array_key_exists('pack_id', $user))
            $userData->pack_id = $user['pack_id'];

        if (array_key_exists('privilege_id', $user))
            $userData->privilege_id = $user['privilege_id'];

        if (array_key_exists('price', $user))
            $userData->price = $user['price'];

        if (array_key_exists('qr_code', $user))
            $userData->qr_code = $user['qr_code'];
        else {
            $qrcode = Utils::generateCode($userData->user_id);
            $userData->qr_code = $qrcode;
        }

        if (array_key_exists('paid', $user)) {
            $userData->isPaid = $user['paid'];
        }

        $userData->save();
        if (array_key_exists('accesss', $user)) {
            foreach ($user['accesss'] as $accessId) {
                if ($accessId != 0) {
                    $accessUser = new UserAccess();
                    $accessUser->access_id = $accessId;
                    $accessUser->user_id = $userData->user_id;
                    $accessUser->save();
                }
            }
        }
    }

    public function getFreeCountByCongressId($congress_id)
    {
        $users = User::where("congress_id", "=", $congress_id)
            ->where("organization_accepted", "=", 1)
            ->get();
        return $users ? count($users) : 0;
    }

    public function getUsersCountByCongressId($congress_id)
    {
        $users = User::where("congress_id", "=", $congress_id)
            ->get();
        return $users ? count($users) : 0;
    }

    public function getUserByNameAndFName($congressId, $first_name, $last_name)
    {
        return User::where('first_name', '=', $first_name)
            ->where('last_name', '=', $last_name)
            ->where('congress_id', '=', $congressId)
            ->first();
    }


    public function getAttestationRequestsByUserId($user_id)
    {
        return AttestationRequest::where("user_id", '=', $user_id)->get()->toArray();
    }

    public function uploadProfilePic($file, $user)
    {
        $timestamp = microtime(true) * 10000;
        $path = $file->storeAs($this->path . $timestamp, $file->getClientOriginalName());

        $user->profile_pic = $path;
        $user->save();

        return $user;
    }

}
