<?php

namespace App\Services;

use App\Models\Access;
use App\Models\AdminCongress;
use App\Models\ConfigCongress;
use App\Models\ConfigSelection;
use App\Models\CongressTheme;
use App\Models\Congress;
use App\Models\Location;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\Organization;
use App\Models\Pack;
use App\Models\Payment;
use App\Models\ConfigSubmission;
use App\Models\User;
use App\Models\UserCongress;
use Illuminate\Support\Facades\Config;
use JWTAuth;
use PDF;


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
        return Congress::where('congress_id','=',$congressId)
        ->with( ['config_selection','evaluation_inscription','users' => function($query) {
            $query->select('User.user_id');
        }])
        ->first();
        
    }

    public function getAll()
    {
        return Congress::all();
    }
    
    public function getMinCongressData() {
        return Congress::select('congress_id','name')->get();
    }
    public function getConfigSubmission($congress_id)
    {
        return ConfigSubmission::where('congress_id', '=', $congress_id)->first();
    }
    
    public function getConfigSelection($congress_id)
    {
        return configSelection::where('congress_id', '=', $congress_id)->first();
    }

    public function getCongressPagination($offset, $perPage, $search)
    {

        $all_congresses = Congress::with([
            "config:congress_id,logo,banner,program_link,status,free",
            "theme:label,description",
            "location.city:city_id,name"
        ])->orderBy('start_date', 'desc')
            ->offset($offset)->limit($perPage)
            ->where('name', 'LIKE', '%' . $search . '%')
            ->orWhere('description', 'LIKE', '%' . $search . '%')
            ->get();
        $congress_renderer = $all_congresses->map(function ($congress) {
            return collect($congress->toArray())
                ->only(["congress_id", "name", "start_date",
                    "end_date", "price", "description", "congress_type_id", "config", "theme", "location"])->all();
        });

        return $congress_renderer;
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
                $query->where('privilege_id', '=', 3);
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
            "config",
            "config_selection",
            "badges" => function ($query) use ($congressId) {
                $query->where('enable', '=', 1)->with(['badge_param:badge_id,key']);
            },
            "packs",
            "accesss.packs" => function ($query) use ($congressId){
                $query->where('congress_id','=',$congressId);                
            },
            "accesss" => function ($query) use ($congressId) {
                $query->where('show_in_register', '=', 1);
                $query->whereNull('parent_id');
            },
            'accesss.participants.user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('privilege_id', '=', 3);
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
                'attestation'
            ])
            ->where("congress_id", "=", $id_Congress)
            ->first();
        return $congress;
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

    function retrieveCongressFromToken()
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
  
    

    public function addCongress($congressRequest, $configRequest, $adminId,$configSelectionRequest)
    {
        $congress = new Congress();
        $congress->name = $congressRequest->input('name');
        $congress->start_date = $congressRequest->input("start_date");
        $congress->end_date = $congressRequest->input("end_date");
        $congress->price = $congressRequest->input('price') && $congressRequest->input('congress_type_id') === '1' ? $congressRequest->input('price') : 0;
        $congress->description = $congressRequest->input('description');
        $congress->congress_type_id = $congressRequest->input('congress_type_id');
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
         $congressRequest->input('congress_type_id') == 2  || 
         ($congressRequest->input('congress_type_id') == 1  &&   $congressRequest->input('withSelection') ) ) {

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
        $admin_congress->privilege_id = 1;
        $admin_congress->save();
        return $congress;
    }


    public function editConfigCongress($configCongress, $configCongressRequest, $congressId,$token)
    {

        //$config_congress = ConfigCongress::where("congress_id", '=', $congressId)->first();

        if (!$configCongress) {
            $configCongress = new ConfigCongress();
        }

        $configCongress->logo = $configCongressRequest['logo'];
        $configCongress->banner = $configCongressRequest['banner'];
        $configCongress->free = $configCongressRequest['free'];
        $configCongress->has_payment = $configCongressRequest['has_payment'];
        $configCongress->is_online = $configCongressRequest['is_online'];
        $configCongress->token_admin = $token ;
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
        $configCongress->currency_code = $configCongressRequest['currency_code'] ;
        $configCongress->lydia_api = $configCongressRequest['lydia_api'];
        $configCongress->lydia_token = $configCongressRequest['lydia_token'];
        $configCongress->is_submission_enabled = $configCongressRequest['is_submission_enabled'];
        $configCongress->register_disabled = $configCongressRequest['register_disabled'];
        $configCongress->update();
        //$this->editCongressLocation($eventLocation, $congressId);

        return $configCongress;
    }
    public function addCongressSubmission($configSubmission,$submissionData,$congressId )
    {
        // add congress submission

        if(!$configSubmission) {
            $configSubmission = new ConfigSubmission();}
        $configSubmission->congress_id = $congressId;
        $configSubmission->max_words = $submissionData['max_words'];
        $configSubmission->num_evaluators = $submissionData['num_evaluators'];
        $configSubmission->start_submission_date = $submissionData['start_submission_date'];
        $configSubmission->end_submission_date = $submissionData['end_submission_date'];
        $configSubmission->save();
        return $configSubmission;

    }
    public function addSubmissionThemeCongress($theme_ids,$congressId )
    {
        $CongressThemes= array();
        CongressTheme::where("congress_id","=",$congressId)->delete();
        foreach ($theme_ids as $theme_id){

            $CongressTheme = new CongressTheme();
            $CongressTheme->congress_id= $congressId;
            $CongressTheme->theme_id= (int) $theme_id;
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

    public function editCongress($congress, $config, $config_selection , $request,$isUpdate)
    {
        $congress->name = $request->input('name');
        $congress->start_date = $request->input('start_date');
        $congress->end_date = $request->input('end_date');
        $congress->price = $request->input('price') && $request->input('congress_type_id') === '1' ? $request->input('price') : 0;
        $congress->congress_type_id = $request->input('congress_type_id');
        $congress->description = $request->input('description');
        $congress->update();

        $config->free = $request->input('config')['free'] ? $request->input('config')['free'] : 0;
        $config->access_system = $request->input('config')['access_system'] ? $request->input('config')['access_system'] : 'Workshop';
        $config->has_payment = $request->input('config')['has_payment'] ? 1 : 0;
        $config->prise_charge_option = $request->input('config')['prise_charge_option'] ? 1 : 0;
        $config->status = $request->input('config')['status'];
        $config->update();

        $config_selection->num_evaluators = $request->input('config_selection')['num_evaluators'] ;
        $config_selection->selection_type = $request->input('config_selection')['selection_type'] ;
        $config_selection->start_date = $request->input('config_selection')['start_date'] ;
        $config_selection->end_date = $request->input('config_selection')['end_date'] ;
        $config_selection->congress_id = $congress->congress_id;
        if ($isUpdate) {
        $config_selection->update();
        } else {
            $config_selection->save();
        }

        return $this->getCongressById($congress->congress_id);
    }

    public function getUsersByStatus($congressId, int $status)
    {
        return User::where('isPresent', '=', $status)
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getLabsByCongress($congressId)
    {
        return Organization::with(['users' => function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        }])->whereHas('users', function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        })->get();
    }

    public function getOrganizationInvoiceByCongress($labId, $congress)
    {
        $lab = $this->organizationServices->getOrganizationById($labId);
        $totalPrice = 0;
        $packs = Pack::whereCongressId($congress->congress_id)->with(['participants' => function ($q) use ($labId) {
            $q->where('User.organization_id', '=', $labId);
        }])->get();
        foreach ($packs as $pack) {
            $packPrice = $pack->price;
            $pack->price = 0;
            foreach ($pack->participants as $participant) {
                $pack->price += $packPrice;
            }
            $totalPrice += $pack->price;
        }
        $today = date('d-m-Y');
        $data = [
            'packs' => $packs,
            'congress' => $congress,
            'today' => $today,
            'lab' => $lab,
            'totalPrice' => $totalPrice,
            'displayTaxes' => false
        ];
        $pdf = PDF::loadView('pdf.invoice.invoice', $data);
        return $pdf->download($lab->name . '_facture_' . $today . '.pdf');
    }

    public function getBadgeByPrivilegeId($congress, $privilege_id)
    {
        for ($i = 0; $i < sizeof($congress->badges); $i++) {
            if ($congress->badges[$i]->privilege_id == $privilege_id && $congress->badges[$i]->enable ==1 ) {
                return $array = [
                    "badge_id_generator" => $congress->badges[$i]->badge_id_generator,
                    "badge_param" => $congress->badges[$i]->badge_param,
                ];

            }
        }
        return null;
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

    function renderMail($template, $congress, $participant, $link, $organization, $userPayment, $linkSondage = null, $linkFrontOffice = null, $linkModerateur = null, $linkInvitees = null, $room = null, $linkFiles=null,$submissionCode = null,
     $submissionTitle = null, $communication_type = null )
    {

        $accesses = "";
        if ($participant && $participant->accesses && sizeof($participant->accesses) > 0) {
            $accesses = "<ul>";
            foreach ($participant->accesses as $access) {
                if ($access->show_in_register == 1 || $access->is_online == 1) {
                    $accessLink = "";
                    if ($congress && $access->is_online == 1) {
                        $accessLink = UrlUtils::getBaseUrlFrontOffice() . '/congress/room/' . $congress->congress_id . '/access/' . $access->access_id;
                        $accessLink = '<a href="'.$accessLink.'" target="_blank"> Lien </a>';
                    }
                    $accesses = $accesses
                        . "<li>" . $access->name
                        . "<span class=\"bold\"> qui se déroulera le "
                        . \App\Services\Utils::convertDateFrench($access->start_date)
                        . " de "
                        . \App\Services\Utils::getTimeFromDateTime($access->start_date)
                        . " à "
                        . \App\Services\Utils::getTimeFromDateTime($access->end_date)
                        . " </span>".$accessLink."</li>";
                }
            }
            $accesses = $accesses . "</ul>";
        }

        if ($congress != null) {
            $startDate = \App\Services\Utils::convertDateFrench($congress->start_date);
            $endDate = \App\Services\Utils::convertDateFrench($congress->end_date);
            $template = str_replace('{{$congress-&gt;start_date}}', $startDate . '', $template);
            $template = str_replace('{{$congress-&gt;end_date}}', $endDate . '', $template);
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
        if ($participant != null)
            $participant->gender = $participant->gender == 2 ? 'Mme.' : 'Mr.';
        return view(['template' => '<html>' . $template . '</html>'], ['congress' => $congress, 'participant' => $participant, 'link' => $link, 'organization' => $organization, 'userPayment' => $userPayment, 'linkSondage' => $linkSondage, 'linkFrontOffice' => $linkFrontOffice, 'linkModerateur' => $linkModerateur, 'linkInvitees' => $linkInvitees, 'room' => $room ,'linkFiles' => $linkFiles,'submission_code' => $submissionCode,'submission_title' => $submissionTitle ,'communication_type' => $communication_type]);
    }

    public
    function getMailType($name,$type = 'event')
    {
        return MailType::where("name", "=", $name)
        ->where('type','=',$type)
        ->first();
    }

    public
    function getMail($congressId, $mail_type_id)
    {
        return Mail::where("congress_id", '=', $congressId)->where('mail_type_id', '=', $mail_type_id)->first();
    }

    public function getMailOutOfCongress($mail_type_id)
    {
        return Mail::where('mail_type_id', '=', $mail_type_id)->first();
    }

    public
    function getMailById($id)
    {
        return Mail::find($id);
    }

    public
    function getAccesssByCongressId($congress_id)
    {
        return Access::with(['participants', 'attestation'])
            ->where('congress_id', '=', $congress_id)
            ->get();
    }

    public
    function getAllCongresses()
    {
        $day = date('Y-m-d', time() + (60 * 60));
        return Congress::with([
            'location.city.country',
            'config',
            'accesss.speakers',
            'accesss.chairs',
            'accesss.sub_accesses',
            'accesss.topic',
            'accesss.type'
        ])
            ->where('end_date', ">=", $day)
            ->get();
    }

    public
    function getCongressConfig($congress_id)
    {
        return ConfigCongress::where('congress_id', '=', $congress_id)->first();
    }

    public
    function getParticipantsCount($congress_id, $privilegeId, $isPresent)
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

    public
    function getConfigLocationByCongressId($congressId)
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

    public function getUserCongress($offset, $perPage, $search, $startDate, $endDate, $status, $user) {
        $congresses = Congress::withCount([
            'submissions' => function($query) use($user) {
            $query->whereHas('user', function($q) use($user){
                $q->where('user_id', '=', $user->user_id);});
            },
            'accesss' => function($query) use($user) {
                $query->whereHas('user_accesss', function($q) use($user){
                    $q->where('user_id', '=', $user->user_id)->where('isPresent','=',1);});
            },
        ])->with('configSubmission:config_submission_id,congress_id',"config:congress_id,logo,banner,program_link,status,free")->whereHas('user_congresses', function($q) use($user){
            $q->where('user_id', '=', $user->user_id);})->orderBy('start_date', 'desc');
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
            $congresses = $congresses->where('end_date', '>', $todayDate)->where('start_date', '<=', $todayDate);;
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
}
