<?php

namespace App\Services;

use App\Models\Access;
use App\Models\AdminCongress;
use App\Models\AllowedOnlineAccess;
use App\Models\ConfigCongress;
use App\Models\ConfigLP;
use App\Models\ConfigSelection;
use App\Models\ConfigSubmission;
use App\Models\Congress;
use App\Models\CongressTheme;
use App\Models\FMenu;
use App\Models\ItemEvaluation;
use App\Models\ItemNote;
use App\Models\Location;
use App\Models\LPSpeaker;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\Payment;
use App\Models\Stand;
use App\Models\Tracking;
use App\Models\User;
use App\Models\UserCongress;
use DateTime;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use App\Models\LPOrganizer;


/**
 * @property OrganizationServices $organizationServices
 */
class CongressServices
{

    public function __construct(OrganizationServices $organizationServices, GeoServices $geoServices)
    {
        $this->organizationServices = $organizationServices;
        $this->geoServices = $geoServices;
    }

    public function getById($congressId)
    {
        return Congress::where('congress_id', '=', $congressId)
            ->with(['config_selection', 'evaluation_inscription', 'users' => function ($query) {
                $query->select('User.user_id');
            }])
            ->first();

    }

    public function getAll()
    {
        return Congress::all();
    }

    public function getMinCongressData()
    {
        return Congress::select('congress_id', 'name')->get();
    }

    public function getConfigSubmission($congress_id)
    {
        return ConfigSubmission::where('congress_id', '=', $congress_id)->first();
    }

    public function addItemsEvaluation($itemsEvaluation, $congress_id)
    {

        foreach ($itemsEvaluation as $itemEvaluation) {

            $item = new ItemEvaluation();
            $item->label = $itemEvaluation['label'];
            $item->ponderation = $itemEvaluation['ponderation'];
            $item->congress_id = $congress_id;
            $item->save();

        }
    }

    public function addItemsNote($itemsNote, $evaluation_inscription_id)
    {

        foreach ($itemsNote as $itemNote) {
            $item = new ItemNote();
            $item->note = $itemNote['note'];
            $item->comment = $itemNote['comment'];
            $item->item_evaluation_id = $itemNote['item_evaluation_id'];
            $item->evaluation_inscription_id = $evaluation_inscription_id;
            $item->save();
        }
    }

    public function getItemsEvaluation($congress_id)
    {
        return ItemEvaluation::where('congress_id', '=', $congress_id)
            ->with(['itemNote'])
            ->get();
    }

    public function getConfigSelection($congress_id)
    {
        return configSelection::where('congress_id', '=', $congress_id)->first();
    }

    public function getCongressPagination($offset, $perPage, $search, $startDate, $endDate, $status, $minPrice, $maxPrice, $type)
    {

        $all_congresses = Congress::with([
            "config:congress_id,logo,banner,program_link,status,free,currency_code",
            "theme:label,description",
            "location.city:city_id,name",
            'admin_congresses' => function ($query) {
                $query->where('privilege_id', '=', config('privilege.Admin'))->with('admin:admin_id,name');
            },
        ])->orderBy('start_date', 'desc')
            ->where('private', '=', 0)
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
                $query->orWhere('name_en', 'LIKE', '%' . $search . '%');
                $query->orWhere('description', 'LIKE', '%' . $search . '%');
                $query->orWhere('description_en', 'LIKE', '%' . $search . '%');
            })
            ->where(function ($query) use ($minPrice, $maxPrice, $startDate, $endDate, $type) {
                if ($startDate != '' && $startDate != 'null') {
                    $query->whereDate('start_date', '>=', date($startDate));
                }

                if ($endDate != '' && $endDate != 'null') {
                    $query->whereDate('end_date', '<=', date($endDate));
                }

                if ($minPrice != '' && $minPrice != 'null') {
                    $query->where('price', '>=', $minPrice);
                }

                if ($maxPrice != '' && $maxPrice != 'null') {
                    $query->where('price', '<=', $maxPrice);
                }

                if ($type != '' && $type != 'null') {
                    if ($type == 1 ) {
                        $query->where('congress_type_id', '=', 1);
                    } if ($type == 2 ){
                        $query->where('congress_type_id', '>=', 2);

                    }

                }

            });
        $all_congresses = $perPage ? $all_congresses->paginate($perPage, ["congress_id", "name", "name_en", "start_date",
            "end_date", "price", "description", "description_en", "congress_type_id"]) : $all_congresses->get();
        return $all_congresses;
    }

    public function getMinimalCongress()
    {

        return Congress::with([
            "mails.type",
            "attestation",
            "badges",
            "accesss",
            "form_inputs.type",
            "form_inputs.values",
            "config",
            "accesss" => function ($query) {
                $query->where('show_in_register', '=', 1);
                $query->whereNull('parent_id');
            },
            'accesss.participants.user_congresses' => function ($query) {
                $query->where('privilege_id', '=', config('privilege.Participant'));
            }
        ])
            ->get();
    }

    public function getMinimalCongressById($congressId)
    {

        return Congress::with([
            "mails.type",
            "attestation",
            "form_inputs.type",
            "form_inputs.values",
            "form_inputs.question_reference"=> function ($query) {
                $query->with(['reference', 
                'response_reference'  => function ($q) {
                    $q->with(['value']);
                } ]);
            },
            "config",
            "config_selection",
            "badges" => function ($query) use ($congressId) {
                $query->where('enable', '=', 1)->with(['badge_param:badge_id,key']);
            },
            "packs",
            "accesss.packs" => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            "accesss" => function ($query) use ($congressId) {
                $query->where('show_in_register', '=', 1);
                $query->whereNull('parent_id');
            },
            'accesss.participants.user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('privilege_id', '=', config('privilege.Participant'));
            }
        ])
            ->where("congress_id", "=", $congressId)
            ->first();
    }

    public function getCongressById($id_Congress)
    {
        $congress = Congress::withCount('users')
            ->with([
                'users.responses.form_input',
                'users.accesses' => function ($query) use ($id_Congress) {
                    $query->where('congress_id', '=', $id_Congress);
                },
                'config',
                'config_selection',
                "badges",
                "attestation",
                "packs.accesses",
                "form_inputs.type",
                "form_inputs.values",
                "mails.type",
                'accesss.attestations',
                'accesss.participants.payments' => function ($query) use ($id_Congress) {
                    $query->where('congress_id', '=', $id_Congress);
                },
                'accesss.participants.user_congresses' => function ($query) use ($id_Congress) {
                    $query->where('congress_id', '=', $id_Congress);
                },
                'ConfigSubmission' => function ($query) use ($id_Congress) {
                    $query->where('congress_id', '=', $id_Congress);
                },
                'location.city.country',
                'accesss.speakers',
                'accesss.chairs',
                'accesss.sub_accesses',
                'accesss.topic',
                'accesss.type',
                'accesss.votes',
                'attestation',
            ])
            ->where("congress_id", "=", $id_Congress)
            ->first();
        return $congress;
    }

    public function getCongressDetailsById($congressId)
    {
        $congress = Congress::withCount('users')
            ->with([
                'config',
            'config_landing',
                'config_selection',
                "packs.accesses",
                'ConfigSubmission' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                },
                'location.city.country',
                'accesss.speakers',
                'accesss.chairs',
                'accesss.sub_accesses',
                'accesss.topic',
                'accesss.type',
            ])
            ->where("congress_id", "=", $congressId)
            ->first();
        return $congress;
    }

    public function getTimePassedInCongressAccessAndStand($users, $congress, $access, $stands)
    {
        $congress['totalTimePassed'] = 0;
        foreach ($users as $user) {
            $user['timePassedCongress'] = 0;
            $usertimePassedPerStand = [];
            $usertimePassedPerAcc = [];
            $timeCongess1 = null;
            $timeCongess2 = null;
            $timeAccess1 = null;
            $timeAccess2 = null;
            $timeStand1 = null;
            $timeStand2 = null;
            foreach ($user->tracking as $key => $tracking) {

                if ($tracking->action_id == 1) {
                    $timeCongess1 = new DateTime($tracking->date);
                }
                if ($tracking->action_id == 2) {
                    $timeCongess2 = new DateTime($tracking->date);
                    if ($timeCongess2 && $timeCongess1) {
                        $interval = $timeCongess2->diff($timeCongess1);
                        $interval = ($interval->s + ($interval->i * 60) + ($interval->h * 3600));
                        $user['timePassedCongress'] += $interval;
                        $timeCongess2 = null;
                        $timeCongess1 = null;
                    }

                }
                if ($tracking->action_id == 3) {
                    $timeAccess1 = new DateTime($tracking->date);
                }

                if ($tracking->access_id && !isset($usertimePassedPerAcc[$tracking->access_id])) {
                    $usertimePassedPerAcc[$tracking->access_id] = ['access_id' => $tracking->access_id, 'timePassed' => 0];
                }

                if ($tracking->stand_id && !isset($usertimePassedPerStand[$tracking->stand_id])) {
                    $usertimePassedPerStand[$tracking->stand_id] = ['stand_id' => $tracking->stand_id, 'timePassed' => 0];
                }

                if ($tracking->action_id == 4) {
                    if ($tracking->access_id) {
                        $timeAccess2 = new DateTime($tracking->date);
                        if ($timeAccess2 && $timeAccess1) {
                            $interval = $timeAccess2->diff($timeAccess1);
                            $interval = ($interval->s + ($interval->i * 60) + ($interval->h * 3600));
                            $usertimePassedPerAcc[$tracking->access_id]['timePassed'] += $interval;
                            $usertimePassedPerAcc[$tracking->access_id]['access_id'] = $tracking->access_id;
                            $timeAccess2 = null;
                            $timeAccess1 = null;
                        }

                    }
                    if ($tracking->stand_id) {

                        $timeStand2 = new DateTime($tracking->date);
                        if ($timeStand1 && $timeStand2) {

                            $interval = $timeStand2->diff($timeStand1);
                            $interval = ($interval->s + ($interval->i * 60) + ($interval->h * 3600));
                            $usertimePassedPerStand[$tracking->stand_id]['timePassed'] += $interval;
                            $usertimePassedPerStand[$tracking->stand_id]['stand_id'] = $tracking->stand_id;

                            $timeStand2 = null;
                            $timeStand1 = null;

                        }

                    }
                }
            }
            $user['timePassedPerStand'] = $usertimePassedPerStand;

            $user['timePassedPerAccess'] = $usertimePassedPerAcc;
            $congress['totalTimePassed'] += $user['timePassedCongress'];
        }

        foreach ($users as $user) {
            foreach ($user['timePassedPerAccess'] as $key => $timeAcc) {

                $left = 0;
                $right = sizeof($access) - 1;
                $index = -1;
                while ($left <= $right) {
                    $midpoint = (int) floor(($left + $right) / 2);

                    if ($access[$midpoint]['access_id'] < $timeAcc['access_id']) {
                        $left = $midpoint + 1;
                    } elseif ($access[$midpoint]['access_id'] > $timeAcc['access_id']) {
                        $right = $midpoint - 1;
                    } else {
                        if (isset($access[$midpoint]['timePassed'])) {
                            $access[$midpoint]['timePassed'] += $timeAcc['timePassed'];
                        } else {
                            $access[$midpoint]['timePassed'] = $timeAcc['timePassed'];
                        }
                        break;
                    }
                }
            }
            foreach ($user['timePassedPerStand'] as $key => $timeStand) {

                $left = 0;
                $right = sizeof($stands) - 1;
                $index = -1;
                while ($left <= $right) {
                    $midpoint = (int) floor(($left + $right) / 2);

                    if ($stands[$midpoint]['stand_id'] < $timeStand['stand_id']) {
                        $left = $midpoint + 1;
                    } elseif ($stands[$midpoint]['stand_id'] > $timeStand['stand_id']) {
                        $right = $midpoint - 1;
                    } else {
                        if (isset($stands[$midpoint]['timePassed'])) {
                            $stands[$midpoint]['timePassed'] += $timeStand['timePassed'];
                        } else {
                            $stands[$midpoint]['timePassed'] = $timeStand['timePassed'];
                        }
                        break;
                    }
                }
            }
        }

        return [$access, $stands, $congress['totalTimePassed']];
    }

    public function getDemoCongress($name)
    {
        $congress = Congress::where("name", "=", $name)
            ->first();
        return $congress;
    }

    public function getCongressConfigById($id_Congress)
    {
        return ConfigCongress::where("congress_id", "=", $id_Congress)
            ->first();
    }

    public function getCongressConfigSubmissionById($congressId)
    {
        return ConfigSubmission::where("congress_id", "=", $congressId)
            ->first();
    }

    public function retrieveCongressFromToken()
    {
        Config::set('jwt.user', 'App\Models\Congress');
        Config::set('jwt.identifier', 'id_Congress');
        try {
            return JWTAuth::parseToken()->toUser();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }

    public function addCongress($congressRequest, $configRequest, $adminId, $configSelectionRequest)
    {
        $congress = new Congress();
        $congress->name = $congressRequest->input('name');
        $congress->start_date = $congressRequest->input("start_date");
        $congress->end_date = $congressRequest->input("end_date");
        $congress->price = $congressRequest->input('price') && $congressRequest->input('congress_type_id') === '1' ? $congressRequest->input('price') : 0;
        $congress->description = $congressRequest->input('description');
        $congress->congress_type_id = $congressRequest->input('congress_type_id');
        $congress->private = $congressRequest->input('private');
        $congress->description_en = $congressRequest->input('description_en');
        $congress->name_en = $congressRequest->input('name_en');
        $congress->save();

        $config = new ConfigCongress();
        $config->congress_id = $congress->congress_id;
        $config->free = $configRequest['free'] ? $configRequest['free'] : 0;
        $config->access_system = $configRequest['access_system'] ? $configRequest['access_system'] : 'Workshop';
        $config->is_submission_enabled = $configRequest['is_submission_enabled'] ? 1 : 0;
        $config->status = $configRequest['status'];
        $config->currency_code = $configRequest['currency_code'];
        $config->save();

        if (
            $congressRequest->input('congress_type_id') == 2 ||
            ($congressRequest->input('congress_type_id') == 1 && $congressRequest->input('withSelection'))) {

            $config_selection = new ConfigSelection();
            $config_selection->congress_id = $congress->congress_id;
            $config_selection->num_evaluators = $configSelectionRequest['num_evaluators'];
            $config_selection->selection_type = $configSelectionRequest['selection_type'];
            $config_selection->start_date = $configSelectionRequest['start_date'];
            $config_selection->end_date = $configSelectionRequest['end_date'];
            $config_selection->save();

        }
        $admin_congress = new AdminCongress();
        $admin_congress->admin_id = $adminId;
        $admin_congress->congress_id = $congress->congress_id;
        $admin_congress->privilege_id = config('privilege.Admin');
        $admin_congress->save();
        return $congress;
    }

    public function editConfigCongress($configCongress, $configCongressRequest, $congressId, $token)
    {
        if (!$configCongress) {
            $configCongress = new ConfigCongress();
        }

        $configCongress->logo = $configCongressRequest['logo'];
        $configCongress->banner = $configCongressRequest['banner'];
        $configCongress->free = $configCongressRequest['free'];
        $configCongress->has_payment = $configCongressRequest['has_payment'];
        $configCongress->is_online = $configCongressRequest['is_online'];
        $configCongress->token_admin = $token;
        $configCongress->program_link = $configCongressRequest['program_link'];
        $configCongress->voting_token = $configCongressRequest['voting_token'];
        $configCongress->prise_charge_option = $configCongressRequest['prise_charge_option'];
        $configCongress->feedback_start = $configCongressRequest['feedback_start'];
        $configCongress->nb_ob_access = $configCongressRequest['nb_ob_access'];
        $configCongress->congress_id = $congressId;
        $configCongress->link_sondage = $configCongressRequest['link_sondage'];
        $configCongress->from_mail = $configCongressRequest['from_mail'];
        $configCongress->replyto_mail = $configCongressRequest['replyto_mail'];
        $configCongress->is_code_shown = $configCongressRequest['is_code_shown'];
        $configCongress->is_notif_register_mail = $configCongressRequest['is_notif_register_mail'];
        $configCongress->is_notif_sms_confirm = $configCongressRequest['is_notif_sms_confirm'];
        $configCongress->mobile_committee = $configCongressRequest['mobile_committee'];
        $configCongress->mobile_technical = $configCongressRequest['mobile_technical'];
        $configCongress->currency_code = $configCongressRequest['currency_code'];
        $configCongress->lydia_api = $configCongressRequest['lydia_api'];
        $configCongress->lydia_token = $configCongressRequest['lydia_token'];
        $configCongress->is_submission_enabled = $configCongressRequest['is_submission_enabled'];
        $configCongress->register_disabled = $configCongressRequest['register_disabled'];
        $configCongress->application = $configCongressRequest['application'];
        $configCongress->max_online_participants = $configCongressRequest['max_online_participants'];
        $configCongress->url_streaming = $configCongressRequest['url_streaming'];
        $configCongress->is_upload_user_img = $configCongressRequest['is_upload_user_img'];
        $configCongress->is_sponsor_logo = $configCongressRequest['is_sponsor_logo'];
        $configCongress->is_phone_required = $configCongressRequest['is_phone_required'];
        $configCongress->nb_max_access = $configCongressRequest['nb_max_access'];
        $configCongress->is_agora = $configCongressRequest['is_agora'];
        $configCongress->meeting_duration = $configCongressRequest['meeting_duration'];
        $configCongress->pause_duration = $configCongressRequest['pause_duration'];
        $configCongress->default_country = $configCongressRequest['default_country'];
        $configCongress->agora_primary_background = $configCongressRequest['agora_primary_background'];
        $configCongress->agora_secondary_background = $configCongressRequest['agora_secondary_background'];
        $configCongress->nb_meeting_table = $configCongressRequest['nb_meeting_table'];
        $configCongress->title_description = $configCongressRequest['title_description'];
        $configCongress->support_img = $configCongressRequest['support_img'];
        if($configCongressRequest['show_in_chat']){
            $showInChat = collect($configCongressRequest['show_in_chat'])->implode(';');
            $configCongress->show_in_chat = $showInChat;
        }
        
        $configCongress->update();

        return $configCongress;
    }

    public function addAllAllowedAccessByCongressId($privilegeIds, $congressId,$access_id=null)
    {
        foreach ($privilegeIds as $privilegeId) {
            $this->addAllowedOnlineAccess($privilegeId, $congressId,$access_id);
        }
    }

    public function addAllowedOnlineAccess($privilege_id, $congress_id,$access_id=null)
    {
        $newAllowedOnlineAccess = new AllowedOnlineAccess();
        $newAllowedOnlineAccess->privilege_id = $privilege_id;
        $newAllowedOnlineAccess->congress_id = $congress_id;
        $newAllowedOnlineAccess->access_id = $access_id;
        $newAllowedOnlineAccess->save();
    }

    public function getAllAllowedOnlineAccess($congress_id,$access_id=null)
    {
        return AllowedOnlineAccess::where('congress_id', '=', $congress_id)->where('access_id', '=', $access_id)

            ->get();
    }

    public function getAllowedOnlineAccessByPrivilegeId($congress_id, $privilege_id,$access_id=null)
    {
        return AllowedOnlineAccess::where('congress_id', '=', $congress_id)
            ->where('privilege_id', '=', $privilege_id)->where('access_id', '=', $access_id)
            ->first();
    }

    public function deleteAllAllowedAccessByCongressId($congress_id,$access_id=null)
    {
        return AllowedOnlineAccess::where('congress_id', '=', $congress_id)->where('access_id','=',$access_id)
            ->delete();
    }

    public function addCongressSubmission($configSubmission, $submissionData, $congressId)
    {
        // add congress submission

        if (!$configSubmission) {
            $configSubmission = new ConfigSubmission();
        }
        $configSubmission->congress_id = $congressId;
        $configSubmission->max_words = $submissionData['max_words'];
        $configSubmission->num_evaluators = $submissionData['num_evaluators'];
        $configSubmission->start_submission_date = $submissionData['start_submission_date'];
        $configSubmission->end_submission_date = $submissionData['end_submission_date'];
        $configSubmission->show_file_upload = $submissionData['show_file_upload'];
        $configSubmission->explanatory_paragraph = $submissionData['explanatory_paragraph'];
        $configSubmission->explanatory_paragraph_en = $submissionData['explanatory_paragraph_en'];
        $configSubmission->save();
        return $configSubmission;

    }

    public function deleteConfigsubmission($configSubmission)
    {
        return $configSubmission->delete();
    }

    public function addSubmissionThemeCongress($theme_ids, $congressId)
    {
        $CongressThemes = array();
        CongressTheme::where("congress_id", "=", $congressId)->delete();
        foreach ($theme_ids as $theme_id) {

            $CongressTheme = new CongressTheme();
            $CongressTheme->congress_id = $congressId;
            $CongressTheme->theme_id = (int) $theme_id;
            $CongressTheme->save();
            array_push($CongressThemes, $CongressTheme);
        }
        return $CongressThemes;
    }

    public function editCongressLocation($configLocation, $configLocationData, $cityId, $congressId)
    {
        // update congress Location
        if (!$configLocation) {
            $configLocation = new Location();
        }
        $configLocation->lng = $configLocationData['lng'];
        $configLocation->lat = $configLocationData['lat'];
        $configLocation->address = $configLocationData['address'];
        $configLocation->city_id = $cityId;
        $configLocation->congress_id = $congressId;
        $configLocation->save();

        return $configLocation;
    }

    public function getCongressAllAccess($adminId)
    {
        $congress = Congress::with(["accesss.participants", "packs.accesses", "form_inputs.type", "users.responses.values", "users.responses.form_input"])
            ->whereHas("admins", function ($query) use ($adminId) {
                $query->where('Admin.admin_id', '=', $adminId);
            })
            ->get();
        //$congress->accesss = $congress->accesses; // ?????
        return $congress;
    }

    public function getCongressByAdmin($adminId)
    {
        $congress = Congress::whereHas("admins", function ($query) use ($adminId) {
            $query->where('Admin.admin_id', '=', $adminId);
        })
            ->get();
        return $congress;
    }

    public function editCongress($congress, $config, $config_selection, $request)
    {
        $congress->name = $request->input('name');
        $congress->start_date = $request->input('start_date');
        $congress->end_date = $request->input('end_date');
        $congress->price = $request->input('price') && $request->input('congress_type_id') === '1' ? $request->input('price') : 0;
        $congress->congress_type_id = $request->input('congress_type_id');
        $congress->description = $request->input('description');
        $congress->private = $request->input('private');
        $congress->description_en = $request->input('description_en');
        $congress->name_en = $request->input('name_en');
        $congress->update();

        $config->free = $request->input('config')['free'] ? $request->input('config')['free'] : 0;
        $config->access_system = $request->input('config')['access_system'] ? $request->input('config')['access_system'] : 'Workshop';
        $config->status = $request->input('config')['status'];
        $config->is_visible_price = $request->input('config')['is_visible_price'];
        $config->update();

        if (isset($request->input('config_selection')['num_evaluators'])) {
            $config_selection->num_evaluators = $request->input('config_selection')['num_evaluators'];
        }

        $config_selection->selection_type = $request->input('config_selection')['selection_type'];
        if (isset($request->input('config_selection')['start_date'])) {
            $config_selection->start_date = $request->input('config_selection')['start_date'];
        }

        if (isset($request->input('config_selection')['end_date'])) {
            $config_selection->end_date = $request->input('config_selection')['end_date'];
        }

        $config_selection->congress_id = $congress->congress_id;
        $config_selection->update();

        return $this->getCongressById($congress->congress_id);
    }

    public function getUsersByStatus($congressId, int $status)
    {
        return User::where('isPresent', '=', $status)
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getBadgeByPrivilegeId($congress, $privilege_id)
    {
        for ($i = 0; $i < sizeof($congress->badges); $i++) {
            if ($congress->badges[$i]->privilege_id == $privilege_id && $congress->badges[$i]->enable == 1) {
                return $array = [
                    "badge_id_generator" => $congress->badges[$i]->badge_id_generator,
                    "badge_param" => $congress->badges[$i]->badge_param,
                ];

            }
        }
        return [
            "badge_id_generator" => null,
            "badge_param" => null
        ];
    }

    public function uploadLogo($file)
    {
        $timestamp = microtime(true) * 10000;
        $path = $file->storeAs('/logo/' . $timestamp, $file->getClientOriginalName());

        return $path;
    }

    public function uploadBanner($file)
    {
        $timestamp = microtime(true) * 10000;
        $path = $file->storeAs('/banner/' . $timestamp, $file->getClientOriginalName());

        return $path;
    }

    public function getEmailById($id)
    {
        return Mail::find($id);
    }
    
    function renderMail($template, $congress, $participant, $link, $organization, $userPayment, $linkSondage = null, $linkFrontOffice = null, $linkModerateur = null, $linkInvitees = null, $room = null, $linkFiles = null, $submissionCode = null,
                        $submissionTitle = null, $communication_type = null, $submissions = [],$submissionComment=null,$linkSubmission=null,$linkPrincipalRoom = null, $meeting=null, $user_receiver=null, $user_sender=null,$verification_code = null, $meetingtable = null, $submissionTheme = null)
    { 
        $accesses = "";
        if ($participant && $participant->accesses && sizeof($participant->accesses) > 0) {
            $accesses = "<ul>";
            foreach ($participant->accesses as $access) {
                if ($access->show_in_register == 1 || $access->is_online == 1) {
                    $accessLink = "";
                    if ($congress && $access->is_online == 1) {
                        $accessLink = UrlUtils::getBaseUrlFrontOffice() . '/room/' . $congress->congress_id . '/access/' . $access->access_id;
                        $accessLink = '<a href="' . $accessLink . '" target="_blank"> Lien </a>';
                    }
                    $accesses = $accesses
                    . "<li>" . $access->name
                    . "<span class=\"bold\"> qui se déroulera le "
                    . \App\Services\Utils::convertDateFrench($access->start_date)
                    . " de "
                    . \App\Services\Utils::getTimeFromDateTime($access->start_date)
                    . " à "
                    . \App\Services\Utils::getTimeFromDateTime($access->end_date)
                        . " </span>" . $accessLink . "</li>";
                }
            }
            $accesses = $accesses . "</ul>";
        }

        $submissionsParms = "";
        if (is_array($submissions)) {
            if (sizeof($submissions) > 0) {
                $submissionsParms = "<ul>";
                foreach ($submissions as $submission) {
                    $type = $submission->communicationType ? $submission->communicationType->label : " ";
                    $submissionsParms = $submissionsParms
                        . "<li>" . $submission->code . ": " . $submission->title . " ( " . $type . " ) " . "</li>";
                }
                $submissionsParms = $submissionsParms . "</ul>";
            }
        }
    
        if ($congress != null) {
            $startDate = \App\Services\Utils::convertDateFrench($congress->start_date);
            $endDate = \App\Services\Utils::convertDateFrench($congress->end_date);
            $template = str_replace('{{$congress-&gt;start_date}}', $startDate . '', $template);
            $template = str_replace('{{$congress-&gt;end_date}}', $endDate . '', $template);
            $congressStartDate=date('d-m-Y', strtotime($congress->start_date)) ;
            $congressEndDate=date('d-m-Y', strtotime($congress->end_date)) ; 
        }
        $template = str_replace('{{$congress-&gt;name}}', '{{$congress->name}}', $template);
        $template = str_replace('{{$congress-&gt;price}}', '{{$congress->price}}', $template);
        $template = str_replace('{{$participant-&gt;first_name}}', '{{$participant->first_name}}', $template);
        $template = str_replace('{{$participant-&gt;last_name}}', '{{$participant->last_name}}', $template);
        $template = str_replace('{{$participant-&gt;gender}}', '{{$participant->gender}}', $template);
        $template = str_replace('{{$userPayment-&gt;price}}', $userPayment ? '{{$userPayment->price}}' : '', $template);
        $template = str_replace('{{$participant-&gt;code}}', '{{$participant->code}}', $template);
        $template = str_replace('{{$participant-&gt;pack-&gt;label}}', '{{$participant->pack->label}}', $template);
        $template = str_replace('{{%24link}}', '{{$link}}', $template);
        $template = str_replace('{{%24linkFrontOffice}}', '{{$linkFrontOffice}}', $template);
        $template = str_replace('{{%24linkPrincipalRoom}}', '{{$linkPrincipalRoom}}', $template);
        $template = str_replace('{{%24linkSondage}}', '{{$linkSondage}}', $template);
        $template = str_replace('{{$participant-&gt;accesses}}', $accesses, $template);
        $template = str_replace('{{$organization-&gt;name}}', '{{$organization->name}}', $template);
        $template = str_replace('{{$organization-&gt;description}}', '{{$organization->description}}', $template);
        $template = str_replace('{{$organization-&gt;email}}', '{{$organization->email}}', $template);
        $template = str_replace('{{$organization-&gt;mobile}}', '{{$organization->mobile}}', $template);
        $template = str_replace('{{$participant-&gt;registration_date}}', date('Y-m-d H:i:s'), $template);
        $template = str_replace('{{$participant-&gt;mobile}}', '{{$participant->mobile}}', $template);
        $template = str_replace('{{$participant-&gt;email}}', '{{$participant->email}}', $template);
        $template = str_replace('{{$room-&gt;name}}', '{{$room->name}}', $template);
        $template = str_replace('{{$participant-&gt;password}}', '{{$participant->passwordDecrypt}}', $template);
        $template = str_replace('{{$submissionComment-&gt;description}}', '{{$submissionComment->description}}', $template);
        $template = str_replace('{{$user_receiver-&gt;last_name}}', '{{$user_receiver->last_name}}', $template);
        $template = str_replace('{{$user_receiver-&gt;first_name}}', '{{$user_receiver->first_name}}', $template);
        $template = str_replace('{{$user_sender-&gt;last_name}}', '{{$user_sender->last_name}}', $template);
        $template = str_replace('{{$user_sender-&gt;first_name}}', '{{$user_sender->first_name}}', $template);
        $template = str_replace('{{$meeting-&gt;start_date}}', '{{$meeting->start_date}}', $template);
        $template = str_replace('{{$meeting-&gt;name}}', '{{$meeting->name}}', $template);
        $template = str_replace('{{%24linkSubmission}}', '{{$linkSubmission}}', $template);

        $linkAccept = $participant != null ? UrlUtils::getBaseUrl() . '/confirm/' . $congress->congress_id . '/' . $participant->user_id . '/1' : null;
        $linkRefuse = $participant != null ? UrlUtils::getBaseUrl() . '/confirm/' . $congress->congress_id . '/' . $participant->user_id . '/-1' : null;
        $template = str_replace('{{$submissionParams}}', $submissionsParms, $template);
        $template = str_replace('{{$buttons}}', '
                                                  <a href="{{$linkAccept}}" style="color:#fff;background-color:#2196f3;width: 60px;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.4375rem .875rem;font-size:.8125rem;line-height:1.5385;border-radius:.1875rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out">Oui</a>
                                                  <a href="{{$linkRefuse}}" style="color:#fff;background-color:#f44336;width: 60px;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.4375rem .875rem;font-size:.8125rem;line-height:1.5385;border-radius:.1875rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out">Non</a>', $template);
        $linkAcceptMeeting =  $meeting != null ? UrlUtils::getBaseUrl() . '/meetings/'. $meeting->meeting_id .'/update-status?status=1&verification_code='.$verification_code : null  ;
        $linkRefuseMeeting =  $meeting != null ? UrlUtils::getBaseUrl() . '/meetings/'. $meeting->meeting_id .'/update-status?status=-1&verification_code='.$verification_code : null ;
        $template = str_replace('{{$meetingButtons}}', '
                                                  <a href="{{$linkAcceptMeeting}}" style="color:#fff;background-color:#2196f3;width: 60px;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.4375rem .875rem;font-size:.8125rem;line-height:1.5385;border-radius:.1875rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out">Oui</a> 
        
                                                  <a href="{{$linkRefuseMeeting}}" style="color:#fff;background-color:#f44336;width: 60px;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.4375rem .875rem;font-size:.8125rem;line-height:1.5385;border-radius:.1875rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out">Non</a>', $template);
        $location=null;
        if($congress->location != null){
            $location=$congress->location->address; 
        }                                  
        $template = str_replace('{{$addToCalendar}}', '<a href="http://www.google.com/calendar/r/eventedit?action=TEMPLATE&text={{$congress->name}}&dates={{$congressStartDate}}/{{$congressEndDate}}&details={{$congress->description}}&location={{$location}}&trp=false&sprop=&sprop=name:" style="color:#fff;background-color:#2196f3;width: 140px;display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.4375rem .875rem;font-size:.8125rem;line-height:1.5385;border-radius:.1875rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out">Ajouter au calendrier </a>', $template);
        if ($participant != null)
            $participant->gender = $participant->gender == 2 ? 'Mme.' : 'Mr.';
        return view(['template' => '<html><head><style> .ql-size-large { font-size: 1.5em; } .ql-size-huge { font-size: 2.5em; } .ql-align-center { text-align:center; } </style></head>' . $template . '</html>'], ['congress' => $congress, 'participant' => $participant, 'link' => $link, 'organization' => $organization, 'userPayment' => $userPayment, 'linkSondage' => $linkSondage, 'linkFrontOffice' => $linkFrontOffice, 'linkModerateur' => $linkModerateur, 'linkInvitees' => $linkInvitees, 'room' => $room, 'linkFiles' => $linkFiles, 'submission_code' => $submissionCode, 'submission_title' => $submissionTitle, 'communication_type' => $communication_type, 'linkAccept' => $linkAccept, 'linkRefuse' => $linkRefuse,'submissionComment' => $submissionComment,'linkSubmission'=> $linkSubmission,'linkPrincipalRoom'=>$linkPrincipalRoom, 'linkAcceptMeeting' => $linkAcceptMeeting, 'linkRefuseMeeting' => $linkRefuseMeeting, 'user_sender' => $user_sender, 'user_receiver' => $user_receiver, 'meeting' => $meeting, 'meetingtable' => $meetingtable,'submissionTheme' => $submissionTheme , 'congressStartDate'=>$congressStartDate,'congressEndDate'=>$congressEndDate, 'location'=>$location]);

    }

    public function getMailType($name, $type = 'event')
    {
        return MailType::where("name", "=", $name)
            ->where('type', '=', $type)
            ->first();
    }

    public function getMail($congressId, $mail_type_id)
    {
        return Mail::where("congress_id", '=', $congressId)->where('mail_type_id', '=', $mail_type_id)->first();
    }

    public function getMailOutOfCongress($mail_type_id)
    {
        return Mail::where('mail_type_id', '=', $mail_type_id)->whereNull('congress_id')->first();
    }

    public function getMailById($id)
    {
        return Mail::find($id);
    }

    public function getAccesssByCongressId($congress_id, $name = null)
    {
        return Access::with(['votes'])->where(function ($query) use ($name) {
            if ($name) {
                $query->where('name', '=', $name);
            }
        })
            ->where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getAllCongresses()
    {
        $day = date('Y-m-d', time() + (60 * 60));
        return Congress::with([
            'location.city.country',
            'config',
            'accesss.speakers',
            'accesss.chairs',
            'accesss.sub_accesses',
            'accesss.topic',
            'accesss.type',
        ])
            ->where('end_date', ">=", $day)
            ->get();
    }

    public function getCongressConfig($congress_id)
    {
        return ConfigCongress::where('congress_id', '=', $congress_id)->first();
    }

    public function getParticipantsCount($congress_id, $privilegeId, $isPresent)
    {
        //participant (privilege= 3)
        return UserCongress::where('congress_id', '=', $congress_id)
            ->where(function ($query) use ($privilegeId) {
                if ($privilegeId) {
                    $query->where('privilege_id', '=', $privilegeId);
                }
                //
            })
            ->where(function ($query) use ($isPresent) {
                if ($isPresent != null) {
                    $query->where('isPresent', '=', $isPresent);
                }
            })
            ->count();
    }

    public function getConfigLocationByCongressId($congressId)
    {
        return Location::where("congress_id", '=', $congressId)
            ->first();
    }

    public function getRevenuCongress($congressId)
    {
        return Payment::where('isPaid', '=', 1)
            ->where('congress_id', '=', $congressId)
            ->sum('price');
    }

    public function getCongressByIdAndRelations($congressId, $relations)
    {
        return Congress::with($relations)
            ->where('congress_id', '=', $congressId)
            ->first();
    }

    public function updateWithParticipantsCount($congress)
    {
        foreach ($congress->accesss as $accesss) {
            $accesss->participant_count = sizeof(array_filter(json_decode($accesss->participants, true), function ($item) {
                return sizeof($item['user_congresses']) > 0;
            }));
            $accesss->unsetRelation('participants');
        }
        return $congress;
    }

    public function getAdminByCongressId($congress_id, $admin)
    {
        return AdminCongress::where('congress_id', '=', $congress_id)
            ->where('admin_id', '=', $admin->admin_id)->first();
    }

    public function getUserCongress($offset, $perPage, $search, $startDate, $endDate, $status, $user)
    {
        $congresses = Congress::withCount([
            'submissions' => function ($query) use ($user) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('user_id', '=', $user->user_id);
                });
            },
            'accesss' => function ($query) use ($user) {
                $query->whereHas('user_accesss', function ($q) use ($user) {
                    $q->where('user_id', '=', $user->user_id)->where('isPresent', '=', 1);
                });
            },
        ])->with('configSubmission:config_submission_id,congress_id', "config:congress_id,logo,banner,currency_code,program_link,status,free", "location", "location.city", "location.city.country")
            ->whereHas('user_congresses', function ($q) use ($user) {
                $q->where('user_id', '=', $user->user_id);
            })->orderBy('start_date', 'desc');
        if ($startDate) {
            $congresses = $congresses->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $congresses = $congresses->where('end_date', '<=', $endDate);
        }
        $todayDate = date("Y-m-d");
        if ($status == "0") {
            $congresses = $congresses->where('end_date', '<=', $todayDate);
        }
        if ($status == "1") {
            $congresses = $congresses->where('end_date', '>', $todayDate)->where('start_date', '<=', $todayDate);
        }
        if ($status == "2") {
            $congresses = $congresses->where('start_date', '>', $todayDate);
        }
        $congresses_filter = $congresses->where('name', 'LIKE', '%' . $search . '%')
            ->orWhere('description', 'LIKE', '%' . $search . '%')
            ->offset($offset)->limit($perPage)
            ->get();
        return $congresses_filter;

    }

    public function confirmPresence($congress_id, $user_id, $will_be_present)
    {
        $userCongress = UserCongress::where('user_id', '=', $user_id)
            ->where('congress_id', '=', $congress_id)
            ->first();
        $userCongress->will_be_present = $will_be_present;
        $userCongress->update();
        return $userCongress;
    }

    public function getStandById($stand_id)
    {
        return Stand::where('congress_id', '=', $stand_id)->get();
    }

    public function modifyAllStatusStand($congressId, $status)
    {
        return Stand::where('congress_id', '=', $congressId)
            ->update(['status' => $status]);
    }

    public function getListTrackingByCongress($congressId, $perPage, $search, $actionId, $accessId, $standId)
    {
        return Tracking::with(['user', 'access', 'stand', 'action', 'user.responses.values', 'user_call'])
            ->whereHas('user', function ($query) use ($search) {
                $query->orwhereRaw('lower(first_name) like (?)', ["%{$search}%"]);
                $query->orWhereRaw('lower(last_name) like (?)', ["%{$search}%"]);
                $query->orWhereRaw('lower(email) like (?)', ["%{$search}%"]);
            })
            ->where(function ($query) use ($actionId, $accessId, $standId) {
                if ($actionId != -1) {
                    $query->where('action_id', '=', $actionId);
                }
                if ($accessId != -1) {
                    $query->where('access_id', '=', $accessId);
                }
                if ($standId != -1) {
                    $query->where('stand_id', '=', $standId);
                }
            })
            ->where('congress_id', '=', $congressId)
            ->paginate($perPage);
    }

    public function setCurrentParticipants($congressId, $nbParticipants)
    {
        return ConfigCongress::where('congress_id', '=', $congressId)
            ->update(['nb_current_participants' => $nbParticipants]);
    }

    public function deleteAllThemes($congressId)
    {
        CongressTheme::where("congress_id", "=", $congressId)->delete();
    }

    public function getConfigLandingPageById($congress_id)
    {
        return ConfigLP::where('congress_id', '=', $congress_id)->first();
    }

    public function getGenericFmenus($congress_id)
    {
        $cacheKey = config('cachedKeys.GenericMenus') . $congress_id;

        if (Cache::has($cacheKey)) {
            $fmenus = Cache::get($cacheKey);
        } else {
            $fmenus = FMenu::where('congress_id', '=', $congress_id)->orderBy('rank', 'ASC')->get();

            if (count($fmenus) == 0) {
                $fmenus =  FMenu::whereNull('congress_id')->orderBy('rank', 'ASC')->get();
            }
            Cache::put($cacheKey, $fmenus, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;
        }

        return  $fmenus;
    }
    
    public function editConfigLandingPage($config_landing_page, $request, $congress_id)
    {
        $no_config = false;
        if (!$config_landing_page) {
            $config_landing_page = new ConfigLP();
            $no_config = true;
        }

        $config_landing_page->congress_id = $congress_id;
        $config_landing_page->header_logo_event = $request->has("header_logo_event") ? $request->input('header_logo_event') : null;
        $config_landing_page->is_inscription = $request->has("is_inscription") ? $request->input('is_inscription') : null;
        $config_landing_page->register_link = $request->has("register_link") ? $request->input('register_link') : null;
        $config_landing_page->home_title = $request->has("home_title") ? $request->input('home_title') : null;
        $config_landing_page->home_description = $request->has("home_description") ? $request->input('home_description') : null;
        $config_landing_page->home_start_date = $request->has("home_start_date") ? $request->input('home_start_date') : null;
        $config_landing_page->home_end_date = $request->has("home_end_date") ? $request->input('home_end_date') : null;
        $config_landing_page->home_banner_event = $request->has("home_banner_event") ? $request->input('home_banner_event') : null;
        $config_landing_page->prp_banner_event = $request->has("prp_banner_event") ? $request->input('prp_banner_event') : null;
        $config_landing_page->prp_title = $request->has("prp_title") ? $request->input('prp_title') : null;
        $config_landing_page->prp_description = $request->has("prp_description") ? $request->input('prp_description') : null;
        $config_landing_page->speaker_title = $request->has("speaker_title") ? $request->input('speaker_title') : null;
        $config_landing_page->speaker_description = $request->has("speaker_description") ? $request->input('speaker_description') : null;
        $config_landing_page->sponsor_description = $request->has("sponsor_description") ? $request->input('sponsor_description') : null;
        $config_landing_page->sponsor_title = $request->has("sponsor_title") ? $request->input('sponsor_title') : null;
        $config_landing_page->prg_title = $request->has("prg_title") ? $request->input('prg_title') : null;
        $config_landing_page->prg_description = $request->has("prg_description") ? $request->input('prg_description') : null;
        $config_landing_page->contact_title = $request->has("contact_title") ? $request->input('contact_title') : null;
        $config_landing_page->contact_description = $request->has("contact_description") ? $request->input('contact_description') : null;
        $config_landing_page->event_link_fb = $request->has("event_link_fb") ? $request->input('event_link_fb') : null;
        $config_landing_page->event_link_instagram = $request->has("event_link_instagram") ? $request->input('event_link_instagram') : null;
        $config_landing_page->event_link_linkedin = $request->has("event_link_linkedin") ? $request->input('event_link_linkedin') : null;
        $config_landing_page->event_link_twitter = $request->has("event_link_twitter") ? $request->input('event_link_twitter') : null;
        $config_landing_page->theme_color = $request->has("theme_color") ? $request->input('theme_color') : null;
        $config_landing_page->theme_mode = $request->has("theme_mode") ? $request->input('theme_mode') : null;
        $config_landing_page->name_partenaire = $request->has("name_partenaire") ? $request->input('name_partenaire') : null;
        $config_landing_page->link_partenaire = $request->has("link_partenaire") ? $request->input('link_partenaire') : null;
        $config_landing_page->show_date = $request->has("show_date") ? $request->input('show_date') : null;
        $config_landing_page->background_color = $request->has("background_color") ? $request->input('background_color') : null;
        $config_landing_page->opacity_color = $request->has("opacity_color") ? $request->input('opacity_color') : null;
        
        $config_landing_page->home_title_en = $request->has("home_title_en") ? $request->input('home_title_en') : null;
        $config_landing_page->home_description_en = $request->has("home_description_en") ? $request->input('home_description_en') : null;
        $config_landing_page->prp_title_en = $request->has("prp_title_en") ? $request->input('prp_title_en') : null;
        $config_landing_page->prp_description_en = $request->has("prp_description_en") ? $request->input('prp_description_en') : null;
        $config_landing_page->speaker_title_en = $request->has("speaker_title_en") ? $request->input('speaker_title_en') : null;
        $config_landing_page->speaker_description_en = $request->has("speaker_description_en") ? $request->input('speaker_description_en') : null;
        $config_landing_page->sponsor_title_en = $request->has("sponsor_title_en") ? $request->input('sponsor_title_en') : null;
        $config_landing_page->sponsor_description_en = $request->has("sponsor_description_en") ? $request->input('sponsor_description_en') : null;
        $config_landing_page->prg_title_en = $request->has("prg_title_en") ? $request->input('prg_title_en') : null;
        $config_landing_page->prg_description_en = $request->has("prg_description_en") ? $request->input('prg_description_en') : null;
        $config_landing_page->contact_title_en = $request->has("contact_title_en") ? $request->input('contact_title_en') : null;
        $config_landing_page->contact_description_en = $request->has("contact_description_en") ? $request->input('contact_description_en') : null;
        $config_landing_page->home_sub_title = $request->has("home_sub_title") ? $request->input('home_sub_title') : null;
        $config_landing_page->home_sub_title_en = $request->has("home_sub_title_en") ? $request->input('home_sub_title_en') : null;
        $config_landing_page->partners_title = $request->has("partners_title") ? $request->input('partners_title') : null;
        $config_landing_page->partners_title_en = $request->has("partners_title_en") ? $request->input('partners_title_en') : null;
        $config_landing_page->partners_description = $request->has("partners_description") ? $request->input('partners_description') : null;
        $config_landing_page->partners_description_en = $request->has("partners_description_en") ? $request->input('partners_description_en') : null;

        $config_landing_page->organizers_title = $request->has("organizers_title") ? $request->input('organizers_title') : null;
        $config_landing_page->organizers_description = $request->has("organizers_description") ? $request->input('organizers_description') : null;
        $config_landing_page->organizers_title_en = $request->has("organizers_title_en") ? $request->input('organizers_title_en') : null;
        $config_landing_page->organizers_description_en = $request->has("organizers_description_en") ? $request->input('organizers_description_en') : null;
        $config_landing_page->page_title = $request->has("page_title") ? $request->input('page_title') : null;
        $config_landing_page->waiting_title = $request->has("waiting_title") ? $request->input('waiting_title') : null;
        $config_landing_page->waiting_desription = $request->has("waiting_desription") ? $request->input('waiting_desription') : null;
        $config_landing_page->opening_date = $request->has("opening_date") ? $request->input('opening_date') : null;
        $config_landing_page->waiting_banner = $request->has("waiting_banner") ? $request->input('waiting_banner') : null;
        $config_landing_page->waiting_title_en = $request->has("waiting_title_en") ? $request->input('waiting_title_en') : null;
        $config_landing_page->waiting_desription_en = $request->has("waiting_desription_en") ? $request->input('waiting_desription_en') : null;

        $no_config ? $config_landing_page->save() : $config_landing_page->update();

        return $config_landing_page;
    }

    public function addLandingPageSpeaker($congress_id, $request)
    {
        $lp_speaker = new LPSpeaker();
        $lp_speaker->congress_id = $congress_id;
        $lp_speaker->first_name = $request->input('first_name');
        $lp_speaker->last_name = $request->input('last_name');
        $lp_speaker->role = $request->input('role');
        $lp_speaker->profile_img = $request->has('profile_img') ? $request->input('profile_img') : '34ZPKTtsyo9ZLPCQ2d2YidDhVedNwFGNfuJDuL45.jpg';
        $lp_speaker->fb_link = $request->input('fb_link');
        $lp_speaker->linkedin_link = $request->input('linkedin_link');
        $lp_speaker->instagram_link = $request->input('instagram_link');
        $lp_speaker->twitter_link = $request->input('twitter_link');
        $lp_speaker->save();
        return $lp_speaker;
    }

    public function getLandingPageSpeakers($congress_id)
    {
        return LPSpeaker::where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getLandingPageSpeakerById($lp_speaker_id)
    {
        return LPSpeaker::where('lp_speaker_id', '=', $lp_speaker_id)
            ->first();
    }

    public function editLandingPageSpeaker($lp_speaker, $request)
    {
        $lp_speaker->first_name = $request->input('first_name');
        $lp_speaker->last_name = $request->input('last_name');
        $lp_speaker->role = $request->input('role');
        $lp_speaker->profile_img = $request->input('profile_img');
        $lp_speaker->fb_link = $request->input('fb_link');
        $lp_speaker->linkedin_link = $request->input('linkedin_link');
        $lp_speaker->instagram_link = $request->input('instagram_link');
        $lp_speaker->twitter_link = $request->input('twitter_link');
        $lp_speaker->update();
        return $lp_speaker;
    }

    public function deleteLandingPageSpeaker($speaker)
    {
        $speaker->delete();
    }

    public function addLandingPageOrganizer($congress_id, $request, $lp_organizer)
    {
        if (!$lp_organizer) {
            $lp_organizer = new LPOrganizer();
        }
        $lp_organizer->congress_id = $congress_id;
        $lp_organizer->full_name = $request->input('full_name');
        $lp_organizer->role = $request->input('role');
        $lp_organizer->profile_img = $request->has('profile_img') ? $request->input('profile_img') : '34ZPKTtsyo9ZLPCQ2d2YidDhVedNwFGNfuJDuL45.jpg';
        $lp_organizer->save();
        return $lp_organizer;
    }

    public function getLandingPageOrganizers($congress_id)
    {
        return LPOrganizer::where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getLandingPageOrganizerById($lp_organizer_id)
    {
        return LPOrganizer::where('lp_organizer_id', '=', $lp_organizer_id)
            ->first();
    }

    public function deleteLandingPageOrganizer($organizer)
    {
        $organizer->delete();
    }

    public function syncronizeLandingPage($congress_id, $congress, $config_congress, $config_landing_page)
    {
        $no_config = false;
        if (!$config_landing_page) {
            $config_landing_page = new ConfigLP();
            $no_config = true;
        }

        $config_landing_page->congress_id = $congress_id;
        $config_landing_page->header_logo_event = $config_congress->logo;
        $config_landing_page->home_title = $congress->name;
        $config_landing_page->home_description = $congress->description;
        $config_landing_page->home_start_date = $congress->start_date;
        $config_landing_page->home_end_date = $congress->end_date;
        $config_landing_page->home_banner_event = $config_congress->banner;
        $config_landing_page->prp_banner_event = $config_congress->banner;

        $no_config ? $config_landing_page->save() : $config_landing_page->update();

        return $config_landing_page;

    }

    public function getParticipantsCachedCount($congress_id)
    {
        $cacheKey = config('cachedKeys.UsersCount') . $congress_id;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $participants = $this->getParticipantsCount($congress_id, null, null);
        Cache::put($cacheKey, $participants, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;
        
        return $participants;
    }
}
