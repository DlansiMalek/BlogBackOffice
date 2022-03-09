<?php

namespace App\Services;

use App\Models\AccessPack;
use App\Models\AccessPresence;
use App\Models\Admin;
use App\Models\AttestationRequest;
use App\Models\Evaluation_Inscription;
use App\Models\FormInputResponse;
use App\Models\FormInput;
use App\Models\Payment;
use App\Models\ResponseValue;
use App\Models\Tracking;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\UserCongress;
use App\Models\ConfigCongress;
use App\Models\UserMail;
use App\Models\UserPack;
use App\Models\WhiteList;
use App\Models\FormInputValue;
use App\Models\UserNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserServices
{

    private $path = 'profile-pic/';

    public function __construct()
    {
        ini_set('max_execution_time', 300);
    }

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

    public function saveUserWithFbOrGoogle($user)
    {
        $name_array = explode(" ", $user->name);
        $first_name = $name_array[0];
        //make sure we get all the rest of his name
        $last_name = '';
        for ($i = 1; $i < count($name_array); $i++) {
            $last_name = $last_name . " " . $name_array[$i];
        }
        $last_name = substr($last_name, 1); //Remove first space

        $newUser = new User();
        $newUser->email = $user->email;
        $newUser->email_verified = 1;
        $newUser->first_name = $first_name;
        $newUser->last_name = $last_name;
        $newUser->passwordDecrypt = app('App\Http\Controllers\SharedController')->randomPassword();
        $newUser->password = app('App\Http\Controllers\SharedController')->encrypt($newUser->passwordDecrypt);
        $newUser->verification_code = Str::random(40);
        $newUser->save();

        if (!$newUser->qr_code) {
            $newUser->qr_code = Utils::generateCode($newUser->user_id);
            $newUser->update();
        }
        
        return $newUser;
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
            $newUser->privilege_id = config('privilege.Participant');
        }

        $newUser->email = $email;

        $newUser->email_verified = 0;
        $newUser->verification_code = Str::random(40);

        /* Generation QRcode */
        $qrcode = Utils::generateCode($newUser->user_id);
        $newUser->qr_code = $qrcode;

        $newUser->congress_id = $request->input("congressId");

        $newUser->save();
        return $this->getUserById($newUser->user_id);
    }

    public function getParticipatorById($user_id)
    {
        $user = User::with([
            'payments', 'accesses', 'responses.values', 'responses.form_input.values',
            'responses.form_input.type'
        ])->where('user_id', '=', $user_id)
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

    public function sendCredentialsOrganizerMail(Admin $admin)
    {
        $email = $admin->email;
        $pathToFile = storage_path() . "/app/badge.png";


        try {
            Mail::send('emailCredentialsOrganizer', [
                'email' => $email, 'password' => $admin->passwordDecrypt
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


        $res = $client->request(
            'POST',
            UrlUtils::getUrlRT() . "/congress/users/send-present",
            [
                'form_params' => [
                    'user' => json_decode(json_encode($participator))
                ]
            ]
        );

        return json_decode($res->getBody(), true);
    }

    public function getParticipatorByIdByCongress($userId, $congressId)
    {
        return User::withCount(['congresses as isPresent' => function ($query) use ($congressId) {
            $query->where("Congress_User . id_Congress", "=", $congressId)
                ->where("Congress_User . isPresent", "=", 1);
        }])->withCount(['congresses as isPaid' => function ($query) use ($congressId) {
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
            $user->privilege_id = config('privilege.Participant');
        }


        $user->qr_code = Str::random(7);
        $user->congress_id = $congress_id;
        $user->payement_type_id = $request->input('payement_type_id');

        $user->save();

        return $this->getUserById($congress_id);
    }

    public function affectAccessIds($user_id, $accessIds)
    {
        foreach ($accessIds as $item) {
            $this->affectAccessById($user_id, $item->access_id);
        }
    }

    public function affectAccess($user_id, $accessIds, $packAccesses)
    {
        foreach ($accessIds as $item) {
            $this->affectAccessById($user_id, $item);
        }

        foreach ($packAccesses as $access) {
            if (!in_array($access->access_id, $accessIds)) {
                $this->affectAccessById($user_id, $access->access_id);
            }
        }
    }

    public function affectPacksToUser($user_id, $packIds = null, $packs = null)
    {

        if ($packIds) {
            $this->AffectPacksToUserWithPackIdsArray($user_id, $packIds);
        } else if ($packs) {
            $this->AffectPacksToUserWithPackArray($user_id, $packs);
        }
    }

    private function AffectPacksToUserWithPackIdsArray($user_id, $packIds)
    {

        foreach ($packIds as $packId) {
            $user_pack = new UserPack();
            $user_pack->user_id = $user_id;
            $user_pack->pack_id = $packId;
            $user_pack->save();
        }
    }

    private function AffectPacksToUserWithPackArray($user_id, $packs)
    {
        foreach ($packs as $pack) {
            $user_pack = new UserPack();
            $user_pack->user_id = $user_id;
            $user_pack->pack_id = $pack['pack_id'];
            $user_pack->save();
        }
    }

    public function getUserIdAndByCongressId($userId, $congressId, $showInRegister = null)
    {
        return User::with([
            "responses.values", 'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_congresses.privilege', "accesses" => function ($query) use ($congressId, $showInRegister) {
                $query->where('congress_id', '=', $congressId);
                if ($showInRegister)
                    $query->where('show_in_register', '=', $showInRegister);
            }
        ])
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


    public function getUsersMinByCongress($congressId, $privilegeId)
    {
        return User::whereHas('user_congresses', function ($query) use ($congressId, $privilegeId) {
            $query->where('congress_id', '=', $congressId);
            if ($privilegeId != null) {
                $query->where('privilege_id', '=', $privilegeId);
            }
        })->with(['profile_img', 'user_congresses', 'responses.form_input', 'responses.values', 'responses.values.val'])->get();
    }
    // public function getUsersCongress($congress_id,$privilegeIds = null){
    //     return User::whereHas('user_congresses', function ($query) use ($congress_id,$privilegeIds) {
    //         $query->where('congress_id', '=', $congress_id);
    //         if ($privilegeIds != null) {
    //             $query->whereIn('privilege_id', $privilegeIds);
    //         }
    //     })->get();
    // }
    public function getUsersByCongress($congressId, $privilegeIds = null, $withAttestation = null, $perPage = null, $search = null, $tri = null, $order = null, $admin_id = null)
    {
        if ($search != "") {
            $payed = Utils::isSimilar($search, "payé", 60);
            $unpayed = Utils::isSimilar($search, "non payé", 75);
            $accepted = Utils::isSimilar($search, "accepted", 60);
            $inProgress = Utils::isSimilar($search, "in progress", 60);
            $refused = Utils::isSimilar($search, "refused", 60);
        } else {

            $payed = $unpayed = $accepted = $inProgress = $refused = null;
        }

        $users = User::whereHas('user_congresses', function ($query) use ($congressId, $privilegeIds) {
            $query->where('congress_id', '=', $congressId);
            if ($privilegeIds != null) {
                $query->whereIn('privilege_id', $privilegeIds);
            }
        })
            ->where(function ($query) use ($admin_id, $congressId) {
                if ($admin_id) {
                    $query->whereHas('inscription_evaluation', function ($query) use ($congressId, $admin_id) {
                        $query->where('admin_id', '=', $admin_id)
                            ->where('congress_id', '=', $congressId);
                    });
                }
            })
            ->with([
                'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }, 'accesses' => function ($query) use ($congressId, $withAttestation) {
                    $query->where('congress_id', '=', $congressId);
                    if ($withAttestation != null) {
                        $query->where("with_attestation", "=", $withAttestation);
                    }
                }, 'accesses.attestations', 'responses' => function ($query) use ($congressId) {
                    $query->whereHas('form_input', function ($query) use ($congressId) {
                        $query->where('congress_id', '=', $congressId);
                    });
                }, 'responses.form_input', 'responses.values', 'responses.values.val', 'organization', 'user_congresses.privilege', 'country', 'payments' => function ($query) use ($congressId, $tri, $order) {
                    $query->where('congress_id', '=', $congressId);
                    if ($tri == 'isPaid')
                        $query->orderBy($tri, $order);
                },
                'inscription_evaluation' => function ($query) use ($congressId, $admin_id) {
                    if ($admin_id) {
                        $query->where('admin_id', '=', $admin_id)->where('congress_id', '=', $congressId);
                    } else {
                        $query->where('congress_id', '=', $congressId);
                    }
                }
            ])
            ->where(function ($query) use ($search, $payed, $unpayed, $accepted, $inProgress, $refused, $congressId) {
                if ($search != "" && !$payed && !$unpayed && !$accepted && !$inProgress && !$refused) {
                    $query->whereRaw('lower(first_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(last_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(email) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(mobile) like (?)', ["%{$search}%"]);
                    $query->orWhereHas('country', function ($q) use ($search) {
                        $q->whereRaw('lower(name) like (?)', ["%{$search}%"]);
                    });
                    $query->orWhereHas('payments', function ($q) use ($search, $congressId) {
                        $q->where('congress_id', '=', $congressId)
                            ->whereRaw('(price) like (?)',  ["%{$search}%"]);
                    });
                }
            });

        if ($search != "" && ($payed || $unpayed)) {
            $users = $users->whereHas('payments', function ($query) use ($search, $congressId, $unpayed) {
                $isPaid = $unpayed ? 0 : 1;
                $query->where('isPaid', '=', $isPaid)->where('congress_id', '=', $congressId);
            });
        }

        if ($search != "" && ($accepted || $inProgress || $refused)) {
            $users = $users->whereHas('user_congresses', function ($query) use ($search, $congressId, $accepted, $inProgress, $refused) {
                $isSelected = $accepted ? 1 : ($inProgress ? 0 : -1);
                $query->where('isSelected', '=', $isSelected)->where('congress_id', '=', $congressId);
            });
        }

        if ($order && ($tri == 'user_id' || $tri == 'country_id' || $tri == 'first_name' || $tri == 'email'
            || $tri == 'mobile')) {
            $users = $users->orderBy($tri, $order);
        }
        if ($order && ($tri == 'type' || $tri == 'date' || $tri == 'status')) {
            $users = $users->join('User_Congress', 'User_Congress.user_id', '=', 'User.user_id')
                ->where('User_Congress.congress_id', '=', $congressId);

            if ($tri == 'type')
                $users->orderBy('privilege_id', $order);
            if ($tri == 'date')
                $users->orderBy('User_Congress.created_at', $order);
            if ($tri == 'status')  
                 $users->orderBy('User_Congress.isSelected', $order);
                
        }
        if ($order && ($tri == 'isPaid' || $tri == 'price')) {
            $users = $users->leftJoin('Payment', 'Payment.user_id', '=', 'User.user_id')
                ->join('User_Congress', 'User_Congress.user_id', '=', 'User.user_id')
                ->where(function ($query) use ($congressId) {
                    $query->where('Payment.congress_id', '=', $congressId)
                        ->where('User_Congress.congress_id', '=', $congressId);
                })
                ->orderBy($tri, $order);
        }

        return $perPage ? $users->paginate($perPage) : $users->get();
    }

    public function getUsersByFilter($congressId, $access = null, $payment = null, $status = null , $questions = null, $perPage = null , $search = null, $questionString = null, $all = 0, $privilegeIds = null, $withAttestation = null, $admin_id = null)
    {
        $users = User::whereHas('user_congresses', function ($query) use ($congressId, $privilegeIds) {
            $query->where('congress_id', '=', $congressId);
            if ($privilegeIds != null) {
                $query->whereIn('privilege_id', $privilegeIds);
            }
        })
            ->where(function ($query) use ($search, $congressId) {
                if ($search) {
                    $query->whereRaw('lower(first_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(last_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(email) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(mobile) like (?)', ["%{$search}%"]);
                    $query->orWhereHas('country', function ($q) use ($search) {
                        $q->whereRaw('lower(name) like (?)', ["%{$search}%"]);
                    });
                    $query->orWhereHas('payments', function ($q) use ($search, $congressId) {
                        $q->where('congress_id', '=', $congressId)
                            ->whereRaw('(price) like (?)',  ["%{$search}%"]);
                    });
                }
            })
            ->where(function ($query) use ($access) {
                if ($access != '' && $access != null) {
                    $query->whereHas('user_access', function ($query) use ($access) {
                            $query->whereIn('access_id', $access);
                        });
                }
            })
            ->where(function ($query) use ($payment) {
                if ($payment != '' && $payment != null) {
                    $query->whereHas('payments', function ($query) use ($payment) {
                        $query->where('isPaid', '=', $payment);
                    });
                }
            })
            ->where(function ($query) use ($questions, $congressId) {
                if (sizeof($questions) != 0) {
                    foreach ($questions as $ques) {
                        $query->whereHas('responses', function ($query) use ($ques) {
                            $query->whereHas('values', function ($q) use ($ques) {
                                $q->where('form_input_value_id', '=', $ques);
                            });
                        });
                    }
                }
            })
            ->where(function ($query) use ($questionString, $congressId) {
                if (sizeof($questionString) != 0 && $questionString[0] != '') {
                    $query->whereHas('responses', function ($query) use ($questionString, $congressId) {
                        $query->where(function ($query) use ($questionString) {
                            foreach ($questionString as $q) {
                                $query->orWhereRaw('lower(response) like (?)', ["%{$q}%"]);
                            }
                        })
                            ->whereHas('form_input', function ($q) use ($congressId) {
                                $q->where('congress_id', '=', $congressId);
                            });
                    });
                }
            })
            ->whereHas('user_congresses', function ($query) use ($status, $congressId) {
            if ($status != '' && $status != null) {
                $query->where('isSelected', '=', $status)->where('congress_id', '=', $congressId);
            }
        })
        ->with([
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'accesses' => function ($query) use ($congressId, $withAttestation) {
                $query->where('congress_id', '=', $congressId);
                if ($withAttestation != null) {
                    $query->where("with_attestation", "=", $withAttestation);
                }
            }, 'accesses.attestations', 'responses' => function ($query) use ($congressId) {
                $query->whereHas('form_input', function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                });
            }, 'responses.form_input', 'responses.values', 'responses.values.val',
            'inscription_evaluation' => function ($query) use ($congressId, $admin_id) {
                    if ($admin_id) {
                        $query->where('admin_id', '=', $admin_id)->where('congress_id', '=', $congressId);
                    } else {
                        $query->where('congress_id', '=', $congressId);
                    }
                },'organization', 'user_congresses.privilege', 'country'
        ]);

        $users = $all == 1 ? $users->get() : $users->paginate($perPage);
      
        return $users;
    }

    public function getAllUsersByCongress($congressId, $privilegeId = null, $isTracked = null)
    {
        $users = User::whereHas('user_congresses', function ($query) use ($congressId, $privilegeId, $isTracked) {
            $query->where('congress_id', '=', $congressId);
            if ($privilegeId != null)
                $query->where('privilege_id', '=', $privilegeId);
            if ($isTracked === 0 || $isTracked === 1)
                $query->where('is_tracked', '=', $isTracked);
        })
            ->with(['user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.values', 'user_congresses.privilege', 'country','user_congresses.organization'])
            ->with(['accesses', 'profile_img'])
            ->get();
        return $users;
    }

    public function getUsersByAccess($congressId, $accessId)
    {
        return User::whereHas('accesses', function ($query) use ($accessId) {
            $query->where('Access.access_id', '=', $accessId);
        })
            ->with([
                'country',
                'payments' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                },
                'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }, 'user_congresses.privilege'
            ])
            ->get();
    }

    public function getPresencesByAccess($accessId)
    {
        return User::join('User_Access', 'User.user_id', '=', 'User_Access.user_id')
            ->where("access_id", '=', $accessId)
            ->where("User_Access.isPresent", "=", 1)
            ->get();
    }

    public function getAllUserAccess($congressId, $userId)
    {
        return User::with([
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }
        ])
            ->where('user_id', '=', $userId)
            ->first();
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

    public function getUsersWithRelations($congressId, $relations, $isPresent, $privilege_ids = [])
    {
        return User::whereHas('user_congresses', function ($query) use ($congressId, $isPresent, $privilege_ids) {
            $query->where('congress_id', '=', $congressId);
            if ($isPresent !== null)
                $query->where('isPresent', '=', $isPresent);
            if (count($privilege_ids) > 0)
                $query->whereIn('privilege_id', $privilege_ids);
        })
            ->with($relations)
            ->get();
    }

    public function getUsersSubmissionWithRelations($congressId, $relations)
    {
        return User::whereHas('submissions', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
            $query->where('status', '=', 1);
        })
            ->with($relations)
            ->get();
    }


    public function getUserByEmailAndCode($email, $code)
    {
        return User::with([
            'user_congresses.congress.accesss.speakers',
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
            'likes'
        ])
            ->whereRaw('lower(email) like (?)', ["{$email}"])
            ->where('code', '=', $code)
            ->first();
    }

    public function deleteUserPacks($userId, $congressId)
    {
        return UserPack::where('user_id', '=', $userId)
            ->whereHas('pack', function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            })
            ->delete();
    }

    public function mappingPeacksourceData($congress, $users)
    {
        $res = array();

        foreach ($users as $user) {
            $channelName = Utils::getChannelNameByUser($user);
            array_push(
                $res,
                array(
                    "user_id" => $user->user_id,
                    "gender" => $user->gender,
                    "name" => $user->last_name . ' ' . $user->first_name,
                    "is_valid" => $this->checkValidUser($congress, $user),
                    "role" => sizeof($user->user_congresses) > 0 ? Utils::getRoleNameByPrivilege($user->user_congresses[0]->privilege_id) : 'PARTICIPANT',
                    "channel_name" => $channelName,
                    "avatar_id" => sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->privilege_id === config('privilege.Organisme') ? $user->avatar_id : null,
                    "authorized_channels" => sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->privilege_id === config('privilege.Participant') ? Utils::mapDataByKey($user->accesses, 'name') : []
                )
            );

            if ($user->img_base64) {
                $res[sizeof($res) - 1]["profile_img"] = $user->img_base64;
            }
        }

        return $res;
    }

    public function addTracking($congressId, $actionId, $userId, $accessId, $standId, $type, $comment, $userCalledId, $date = null)
    {
        $tracking = new Tracking();

        $tracking->congress_id = $congressId;
        $tracking->action_id = $actionId;
        $tracking->user_id = $userId;
        $tracking->access_id = $accessId;
        $tracking->stand_id = $standId;
        $tracking->type = $type;
        $tracking->comment = $comment;
        $tracking->user_call_id = $userCalledId;
        $tracking->date = $date ? $date : date('Y-m-d H:i:s');

        $tracking->save();

        return $tracking;
    }

    public function closeTracking($congressId, $userId)
    {
        $tracking = $this->getLastTracking($congressId, $userId);

        if ($tracking) {
            if ($tracking->action_id === 3)
                $this->addTracking($congressId, 4, $userId, $tracking->access_id, $tracking->stand_id, $tracking->type, 'FOCED CLOSE', null, $tracking->date);
            if ($tracking->action_id !== 2)
                $this->addTracking($congressId, 2, $userId, null, null, null, 'FOCED CLOSE', null, $tracking->date);
        }
    }

    public function addPrincipalUserAuthorExternal($data)
    {
        $email = $data['author_email'];
        $firstName = isset($data['author_first_name']) ? $data['author_first_name'] : 'Participant';
        $lastName = isset($data['author_last_name']) ? $data['author_last_name'] : 'Eventizer';
        $gender = isset($data['author_gender']) ? $data['author_gender'] : 1;
        $mobile = isset($data['author_mobile']) ? $data['author_mobile'] : '77777777';

        $countryId = "TUN";

        return $this->createOrUpdateUser($email, $firstName, $lastName, $gender, $mobile, $countryId);
    }

    public function isUserModerator($privilege)
    {
        return $privilege->privilege_id   == config('privilege.Moderateur')
            || $privilege->privilege_id   == config('privilege.Conferencier_Orateur')
            || $privilege->priv_reference == config('privilege.Moderateur')
            || $privilege->priv_reference == config('privilege.Conferencier_Orateur');
    }

    private function sendingRTAccess($user, $accessId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request(
            'POST',
            UrlUtils::getUrlRT() . '/congress/users/send-present-access',
            [
                'form_params' => [
                    'user' => json_decode(json_encode($user)),
                    'accessId' => $accessId
                ]
            ]
        );

        return json_decode($res->getBody(), true);
    }

    public function sendingToAdmin($allParticipants, $congressId)
    {
        $client = new \GuzzleHttp\Client();


        $res = $client->request(
            'POST',
            UrlUtils::getUrlRT() . '/congress/users/send-all',
            [
                'form_params' => [
                    'users' => json_decode(json_encode($allParticipants)),
                    'congressId' => $congressId
                ]
            ]
        );

        return json_decode($res->getBody(), true);
    }

    public function getUserByEmail($email, $congress_id = null)
    {
        $email = strtolower($email);
        return User::whereRaw('lower(email) = (?)', ["{$email}"])
        ->with(['user_congresses' => function ($query) use ($congress_id) {
            if ($congress_id) {
                $query->where('congress_id', '=', $congress_id);
            }
        }, 'profile_img'])
            ->first();
    }

    public function getUserByVerificationCodeAndId($code, $user_id)
    {
        $conditions = ['verification_code' => $code, 'user_id' => $user_id];
        return User::where($conditions)->first();
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
            $newUser->privilege_id = config('privilege.Participant');
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
            $newUser->privilege_id = config('privilege.Participant');
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

    public function saveUsersFromExcel($congress_id, $users)
    {
        foreach ($users as $user) {
            $this->addUserExcel($congress_id, $user);
        }
    }

    public function getFastUsersByCongressId($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('privilege_id', '=', config('privilege.Participant'))
            ->get();
    }

    public function getUsersEmailAttestationNotSendedByCongress($congressId)
    {
        return User::where('congress_id', '=', $congressId)
            ->where('email_attestation_sended', '=', 0)
            ->get();
    }

    public function updateUserPayment($userPayment, $path)
    {
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

    public function saveUser(Request $request, $resource = null)
    {
        $user = new User();
        $user->email = $request->email;

        if ($request->has('password') && $request->input('password') != "") {
            $password = $request->input('password');
        } else {
            $password = Str::random(8);
        }

        if ($request->has('first_name')) $user->first_name = $request->input('first_name');
        if ($request->has('last_name')) $user->last_name = $request->input('last_name');
        if ($request->has('gender')) $user->gender = $request->input('gender');
        if ($request->has('mobile')) $user->mobile = $request->input('mobile');
        $user->passwordDecrypt = $password;
        $user->password = bcrypt($password);
        if ($request->has('country_id')) $user->country_id = $request->country_id;
        if ($request->has('avatar_id')) $user->avatar_id = $request->input('avatar_id');
        if ($request->has('resource_id')) {
            $user->resource_id = $request->input('resource_id');
            /** TODO fix data too long */
            /*if($resource)
                $user->img_base64 = Utils::getBase64Img(UrlUtils::getFilesUrl() . $resource->path);*/
        }
        $user->verification_code = Str::random(40);
        $user->save();
        if (!$user->qr_code) {
            $user->qr_code = Utils::generateCode($user->user_id);
            $user->update();
        }

        return $user;
    }

    public function editUser(Request $request, $user, $resource = null)
    {
        $user->email = $request->email;

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->passwordDecrypt = $request->input('password');
        }

        if ($request->has('first_name')) $user->first_name = $request->input('first_name');
        if ($request->has('last_name')) $user->last_name = $request->input('last_name');
        if ($request->has('gender')) $user->gender = $request->input('gender');
        if ($request->has('mobile')) $user->mobile = $request->input('mobile');
        if ($request->has('country_id')) $user->country_id = $request->country_id;
        if ($request->has('resource_id')) $user->resource_id = $request->input('resource_id');
        if ($request->has('avatar_id')) $user->avatar_id = $request->input('avatar_id');

        $user->update();
        return $user;
    }

    public function getUserCongress($congress_id, $user_id)
    {
        return UserCongress::where('user_id', '=', $user_id)
            ->where('congress_id', '=', $congress_id)
            ->with(['congress', 'congress.config_selection' => function ($query) {
                $query->select(['congress_id', 'selection_type']);
            }, 'user'])
            ->first();
    }

    public function getUsersTracking($congress_id, $actionsId = null, $privilegeId = null)
    {
        return User::whereHas('user_congresses', function ($query) use ($congress_id, $privilegeId) {
            $query->where('congress_id', '=', $congress_id);
            if ($privilegeId != null) {
                $query->where('privilege_id', '=', $privilegeId);
            }
        })->with(['tracking' => function ($query) use ($congress_id, $actionsId) {
            $query->where('congress_id', '=', $congress_id);
            if (sizeof($actionsId) > 0)
                $query->whereIn('action_id', $actionsId);
            $query->orderBy('date');
        }, 'tracking.access', 'tracking.stand'])
            ->get();
    }

    public function getUsersCongressByCongressId($congress_id)
    {
        return UserCongress::where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getUserCongressByUserId($userId)
    {
        return UserCongress::where('user_id', '=', $userId)
            ->get();
    }

    public function getQuestionByKey($congress_id,$key)
    {
        return FormInput::where('congress_id', '=', $congress_id)
         ->where('key','=',$key)
         ->first();
    }

    public function getValueResponse($user_id, $form_input_id)
    {
        return FormInputResponse::where('user_id', '=', $user_id)
        ->where('form_input_id', '=', $form_input_id) 
        ->with(['values'  => function ($query) {
            $query->with(['val']);
        }]) 
        ->get();
    }

    public function getResponseFormInput($user_id, $form_input_id)
    {
        return FormInputResponse::where('user_id', '=', $user_id)
            ->where('form_input_id', '=', $form_input_id)   
            ->get('response');
    }


    public function saveUserCongress($congress_id, $user_id, $privilege_id, $organization_id, $pack_id, $isSelected = 0)
    {
        $user_congress = new UserCongress();
        $user_congress->user_id = $user_id;
        $user_congress->congress_id = $congress_id;
        $user_congress->privilege_id = $privilege_id;
        $user_congress->isSelected = $isSelected;

        if ($organization_id)
            $user_congress->organization_id = $organization_id;
        if ($pack_id)
            $user_congress->pack_id = $pack_id;

        $user_congress->save();
        return $user_congress;
    }

    public function affectNoteToUser($evaluation, $note, $commentaire)
    {
        $evaluation->note = $note;
        $evaluation->commentaire = $commentaire;
        $evaluation->update();
        return $evaluation;
    }

    public function changeUserStatus($user_congress, $status)
    {
        $user_congress->isSelected = $status;
        $user_congress->update();
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
        return User::with([
            'user_congresses.congress.accesss.speakers',
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
            'likes'
        ])
            ->where('qr_code', '=', $qrCode)
            ->first();
    }

    public function getUserByIdWithRelations($userId, $relations)
    {
        return User::with($relations)
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function getEvaluationInscriptionByUserIdAndAdminId($user_id, $congress_id, $admin_id)
    {
        $conditionsToMatch = ['user_id' => $user_id, 'congress_id' => $congress_id, 'admin_id' => $admin_id];
        return Evaluation_Inscription::where($conditionsToMatch)->first();
    }

    public function getAllEvaluationInscriptionByUserId($user_id, $congress_id)
    {
        return Evaluation_Inscription::where('user_id', '=', $user_id)->where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getUserById($userId)
    {
        return User::with([
            'user_congresses.congress.accesss.speakers',
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
            'likes',
            'profile_img'
        ])
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function getAverageNote($user_id, $congress_id)
    {
        return Evaluation_Inscription::where('user_id', '=', $user_id)->where('congress_id', '=', $congress_id)
            ->where('note', '>', -1)
            ->average('note');
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
            ->with(['congress'])
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

    public function deleteAccessById($user_id, $accessId)
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

    public function retrieveUserFromToken()
    {
        try {
            return auth()->user();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $refreshed = JWTAuth::refresh(JWTAuth::getToken());
            $user = JWTAuth::setToken($refreshed)->toUser();
            header('Authorization: Bearer ' . $refreshed);
            return $user;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }

    public function calculateCongressFees($congress, $pack, $accesses)
    {
        if ($congress->congress_type_id == 2 || $congress->congress_type_id == 3) {
            return 0;
        }

        $price = 0;
        if ($congress->price) {
            $price += $congress->price;
        }
        if ($pack) {
            $price += $pack->price;
        }
        if ($accesses) {
            foreach ($accesses as $access) {
                if ($pack) {
                    if (!$accessInPack = AccessPack::where('pack_id', '=', $pack->pack_id)->where('access_id', '=', $access->access_id)->first()) {
                        $price += $access->price;
                    }
                } else {
                    $price += $access->price;
                }
            }
        }
        return $price;
    }

    public function getRefusedParticipants($congressId, $emails_array)
    {
        //users id who are registred in the congress
        $accepted_user_id_array = UserCongress::select('user_id')->where('congress_id', '=', $congressId)
            ->where('privilege_id', '=', config('privilege.Participant'));
        //users who got refused with mails refused
        return User::with(['user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])->whereIn('user_id', $accepted_user_id_array)
            ->whereNotIn('email', $emails_array)->get();
    }

    private function getLastTracking($congressId, $userId)
    {
        return Tracking::where('user_id', '=', $userId)
            ->where('congress_id', '=', $congressId)
            ->latest()->get()->first();
    }

    private function checkValidUser($congress, $user)
    {

        if ($congress->congress_type_id === 3) { // Valid if congress is free without selection
            return true;
        }
        // check if isSelected = 1
        // check if isPaid = 1
        if (sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->isSelected == 1) {
            return true;
        }

        if (sizeof($user->payments) > 0 && $user->payments[0]->isPaid === 1) {
            return true;
        }

        return false;
    }

    public function createOrUpdateUser($email, $firstName, $lastName, $gender, string $mobile, $countryId)
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            $user = new User();
        }

        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->gender = $gender;
        $user->mobile = $mobile;
        $user->country_id = $countryId;

        if (!$user->password) {
            $password = Str::random(8);
            $user->passwordDecrypt = $password;
            $user->password = bcrypt($password);
        }


        $user->save();
        if (!$user->qr_code) {
            $user->qr_code = Utils::generateCode($user->user_id);
            $user->update();
        }
        return $user;
    }

    public function getWhiteList($congress_id, $perPage, $search)
    {
        $whiteLists = WhiteList::where('congress_id', '=', $congress_id)
            ->where(function ($query) use ($search) {
                if ($search != "") {
                    $query->whereRaw('lower(first_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(last_name) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(email) like (?)', ["%{$search}%"]);
                }
            });
        return $whiteLists->paginate($perPage);
    }

    public function addWhiteList($congress_id, $email, $first_name, $last_name, $mobile)
    {
        $whiteList = new WhiteList();
        $whiteList->congress_id = $congress_id;
        $whiteList->email = $email;
        if ($first_name)
            $whiteList->first_name = $first_name;
        if ($last_name)
            $whiteList->last_name = $last_name;
        if ($mobile)
            $whiteList->mobile = $mobile;
        $whiteList->save();
    }

    public function getWhiteListByEmailAndCongressId($email, $congress_id)
    {
        $email = strtolower($email);
        return WhiteList::where('congress_id', '=', $congress_id)
            ->whereRaw('lower(email) = (?)', ["{$email}"])
            ->first();
    }

    public function getWhiteListById($white_list_id)
    {
        return WhiteList::where('white_list_id', '=', $white_list_id)
            ->first();
    }

    public function deleteWhiteList($white_list)
    {
        $white_list->delete();
    }

    public function getUsersWithResources($congressId)
    {
        return User::whereHas('user_congresses', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        })
            ->whereNotNull('resource_id')
            ->whereNull('img_base64')
            ->with('profile_img')
            ->get();
    }

    public function addUserFromExcel($userData, $pass = null, $resourceId = null)
    {
        $password = $pass ? $pass : Str::random(8);
        $user = new User();

        $user->email = $userData['email'];
        $user->first_name = isset($userData['first_name']) ? $userData['first_name'] : '-';
        $user->last_name = isset($userData['last_name']) ? $userData['last_name'] : '-';
        if (array_key_exists("mobile", $userData))
            $user->mobile = $userData['mobile'];
        $user->passwordDecrypt = $password;
        $user->password = bcrypt($password);
        $user->email_verified = 1;
        if ($resourceId) {
            $user->resource_id = $resourceId;
        }
        $user->save();
        if (!$user->qr_code) {
            $user->qr_code = Utils::generateCode($user->user_id);
            $user->update();
        }
        return $user;
    }

    public function addResponseValue($form_input_response_id, $form_input_value_id)
    {
        $repVal = new ResponseValue();
        $repVal->form_input_response_id = $form_input_response_id;
        $repVal->form_input_value_id = $form_input_value_id;
        $repVal->save();
    }

    public function getFormInputValues($form_input_id)
    {
        return FormInputValue::where('form_input_id', '=', $form_input_id)
            ->get();
    }


    public function isUserModeratorStand($userCongress)
    {
        return $userCongress->privilege_id == config('privilege.Organisme');
    }

    public function deleteFormInputUserByKey($userId, $congressId, $key)
    {
        return FormInputResponse::whereHas('form_input', function ($query) use ($congressId, $key) {
            $query->where('congress_id', '=', $congressId)
                ->where('key', '=', $key);
        })->where("user_id", '=', $userId)->delete();
    }

    public function isUserOrganizer($userCongress)
    {
        return $userCongress->privilege_id == config('privilege.Organisateur');
    }

    public function getUser3DByEmail($email)
    {
        $email = strtolower($email);
        $user = User::whereRaw('lower(email) = (?)', ["{$email}"])
            ->with([
                'profile_img',
                'user_congresses.congress.config' => function ($query) {
                    $query->select('config_congress_id', 'congress_id', 'logo', 'banner', 'url_streaming');
                },
                'user_congresses.congress' => function ($query) {
                    $query->select('congress_id', 'name', 'start_date', 'end_date', 'description');
                }
            ])
            ->select('user_id', 'first_name', 'last_name', 'gender', 'mobile', 'email', 'resource_id')
            ->first();

        return $user;
    }
    public function editUserPrivilege($userCongress, $privilege_id)
    {
        $userCongress->privilege_id = $privilege_id;
        $userCongress->update();
    }

    public function addUserFromExcelOrgnization($user, $userData)
    {
        $password = Str::random(8);
        if (!$user) {
            $user = new User();
        }


        $user->email = $userData['admin_email'];
        $name = explode(" ", $userData['admin_name']);
        $user->first_name = isset($name[0]) ? $name[0] : '-';
        $user->last_name  = isset($name[1]) ? $name[1] : '-';
        $user->mobile = isset($userData['admin_mobile']) ? $userData['admin_mobile'] : null;
        $user->passwordDecrypt = $password;
        $user->password = bcrypt($password);
        $user->email_verified = 1;
        $user->save();
        if (!$user->qr_code) {
            $user->qr_code = Utils::generateCode($user->user_id);
            $user->update();
        }
        return $user;
    }
    public function addUserCongressFromExcelOrgnization($userCongress, $user_id, $congressId, $privilegeId)
    {
        if (!$userCongress) {
            $userCongress = new UserCongress();
        }
        $userCongress->congress_id = $congressId;
        $userCongress->user_id = $user_id;
        $userCongress->privilege_id = $privilegeId;    //privilege Organisme
        $userCongress->save();
        return $userCongress;
    }

    public function getAllUsersByCongressFrontOfficeWithPagination($congressId, $perPage, $search, $user_id)
    {
        $users = User::whereHas('user_congresses', function ($query) use ($congressId, $search, $user_id) {
            $query->where('congress_id', '=', $congressId);
            $query->where('user_id', '!=', $user_id);

            if ($search != "") {
                $query->where(DB::raw('CONCAT(first_name," ",last_name)'), 'like', '%' . $search . '%');
            }
        })
            ->with(['user_congresses'=> function ($query) use ($congressId){
                $query->where('congress_id', '=', $congressId);
            }])->with('profile_img')
            ->paginate($perPage);
        return  $users;
    }

    public function getShowInChat($congress_id)
    {
        $show_in_chat =ConfigCongress::where('congress_id', '=', $congress_id)
            ->get('show_in_chat')->pluck('show_in_chat'); 
        $show_in_chat = substr($show_in_chat,2);
        $show_in_chat=substr_replace($show_in_chat ,"", -2);
        return  $show_in_chat ;    
        }

    public function getAllUsersByCongressWithSameResponse($congressId,$formInputResponseId, $formInputValueId,$privilegIds = [],$mailId)
    {
         $users = User::whereHas(
            'responses.values', function ($query) use ($formInputResponseId, $formInputValueId) {
                $query->where('form_input_id', '=', $formInputResponseId);
                if (count($formInputValueId) > 0) {
                    $query->whereIn('form_input_value_id', $formInputValueId);
                }
            }
        )
        ->with(['user_mails' => function ($query) use ($mailId) {
            $query->where('mail_id', '=', $mailId);
        },  'accesses' => function ($query) use ($congressId) {
            $query->where("congress_id", "=", $congressId);
        },
        'user_congresses' => function ($query) use ($congressId,$privilegIds) {
            $query->where('congress_id', '=', $congressId);
            if (count($privilegIds) > 0 )
                $query->whereIn('privilege_id', $privilegIds);
        },
        'payments' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        } ])->get();
        return $users;
    }

    public function addUserNetwork($user_id, $fav_id) 
    {
        $user_network = new UserNetwork();
        $user_network->user_id = $user_id;
        $user_network->fav_id = $fav_id;
        $user_network->save();
        return $user_network;
    }

    public function getUserNetwork($user_id, $fav_id)
    {
        return UserNetwork::where('user_id', '=', $user_id)
            ->where('fav_id', '=', $fav_id)
            ->first();
    }

    public function getAllUserNetwork($user_id)
    {
        return UserNetwork::where('user_id', '=', $user_id)
            ->with(['fav' => function ($query) {
                $query->with('profile_img');
            }])
            ->get();
    }

    public function getUserNetworkById($user_network_id)
    {
        return UserNetwork::where('user_network_id', '=', $user_network_id)
            ->first();
    }

    public function deleteUserNetwork($user_network)
    {
        return $user_network->delete();
    }

    public function getCachedUsers($congress_id, $page, $perPage ,$search,$userId)
{
    $cacheKey = config('cachedKeys.Users') . $congress_id . $page . $perPage . $search . $userId ;

    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }

    $users = $this->getAllUsersByCongressFrontOfficeWithPagination($congress_id,$perPage,$search, $userId);
    Cache::put($cacheKey, $users, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;

    return $users;
}
    public function getUsersInformations($congressId,$perPage , $search,$user_id )
    {
        $users = DB::table('User')   
        ->leftJoin('Country','User.country_id','=','Country.alpha3code')
        ->join('User_Congress','User.user_id','=','User_Congress.user_id') 
        ->where( function ($query) use ($congressId, $search) {
            if ($search != "") {
                $query->where(DB::raw('CONCAT(first_name," ",last_name)'), 'like', '%' . $search . '%');
            }

            if ($congressId != 'null' && $congressId  ) {
                $query->where(DB::raw('User_Congress.congress_id'), '=', $congressId );
            } 
             })
        
        ->select('User.user_id','first_name','last_name','Country.name','mobile','email','passwordDecrypt')
        ->groupBy('User.user_id','first_name','last_name','Country.name','mobile','email','passwordDecrypt');
        
        return  $users->paginate($perPage);  
    }

    public function getRandomUsers($congressId,$user_id)
    {
        $users = User::whereHas('user_congresses', function ($query) use ($congressId, $user_id) {
            $query->where('congress_id', '=', $congressId);
            $query->where('user_id', '!=', $user_id);

        })
            ->with(['user_congresses'=> function ($query) use ($congressId){
                $query->where('congress_id', '=', $congressId);
            }])
            ->with('profile_img')
            ->get();

            if(count($users)>= 4){
                $users= $users->random(4);
            }
        return  $users;

    }

}


