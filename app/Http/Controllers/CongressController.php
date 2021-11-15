<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\AdminCongress;
use App\Models\Badge;
use App\Models\ConfigCongress;
use App\Models\ConfigSelection;
use App\Models\User;
use App\Models\UserCongress;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\GeoServices;
use App\Services\MailServices;
use App\Services\NotificationServices;
use App\Services\PackServices;
use App\Services\PaymentServices;
use App\Services\PrivilegeServices;
use App\Services\ResourcesServices;
use App\Services\RoomServices;
use App\Services\SharedServices;
use App\Services\StandServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use App\Services\TrackingServices;
use App\Services\FMenuServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\MeetingServices;

class CongressController extends Controller
{

    protected $congressServices;
    protected $adminServices;
    protected $accessServices;
    protected $privilegeServices;
    protected $userServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $packService;
    protected $resourceService;
    protected $geoServices;
    protected $mailServices;
    protected $paymentServices;
    protected $notificationService;
    protected $roomServices;
    protected $standServices;
    protected $trackingServices;
    protected $fmenuServices;
    protected $meetingServices;

    function __construct(CongressServices $congressServices, AdminServices $adminServices,
                         AccessServices $accessServices,
                         PrivilegeServices $privilegeServices,
                         UserServices $userServices,
                         SharedServices $sharedServices,
                         BadgeServices $badgeServices,
                         PackServices $packService,
                         GeoServices $geoServices,
                         StandServices $standServices,
                         MailServices $mailServices,
                         RoomServices $roomServices,
                         NotificationServices $notificationService,
                         ResourcesServices $resourceService,
                         PaymentServices $paymentServices,
                         TrackingServices $trackingServices,
                         FMenuServices $fmenuServices,
                         MeetingServices $meetingServices )
    {
        $this->congressServices = $congressServices;
        $this->geoServices = $geoServices;
        $this->adminServices = $adminServices;
        $this->notificationService = $notificationService;
        $this->accessServices = $accessServices;
        $this->privilegeServices = $privilegeServices;
        $this->userServices = $userServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->roomServices = $roomServices;
        $this->packService = $packService;
        $this->resourceService = $resourceService;
        $this->mailServices = $mailServices;
        $this->paymentServices = $paymentServices;
        $this->standServices = $standServices;
        $this->trackingServices = $trackingServices;
        $this->fmenuServices = $fmenuServices;
        $this->meetingServices = $meetingServices;
    }


    public function addCongress(Request $request)
    {
        if (!$request->has(['name', 'start_date', 'end_date', 'price', 'config']))
            return response()->json(['message' => 'bad request'], 400);
        $admin = $this->adminServices->retrieveAdminFromToken();
        $congress = $this->congressServices->addCongress(
            $request,
            $request->input('config'),
            $admin->admin_id,
            $request->input('config_selection')
        );
        $tack1 = $this->trackingServices->createIndexByCongress($congress->congress_id);
        $tack2 = $this->trackingServices->enrichPolicyByCongress($congress->congress_id);
        $tack4 = $this->trackingServices->executePolicy($congress->congress_id);
        $tack3 = $this->trackingServices->enrichPolicyByUserDetails($congress->congress_id);
        return $congress;
    }

    public function editStatus(Request $request, $congressId, $status)
    {
        $presence = $request->query('presence');
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $configCongress = $this->congressServices->getCongressConfigById($congressId);
        if ($presence) {
            $configCongress->auto_presence = $status;
        } else {
            $configCongress->status = $status;
        }
        $configCongress->update();
        if ($status == 0)
            $execute = $this->trackingServices->executePolicy($congressId);

        return response()->json(['message' => 'auto presence updating']);
    }

    public function switchUsersRoom($congressId, Request $request)
    {

        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(['response' => 'congress not found'], 404);
        }

        $event = $request->input('event');
        $usersToken = $this->notificationService->getAllKeysByCongressIdAndSource($congressId, 'frontOffice');
        foreach ($usersToken as $userToken) {
            if ($event == 'distribute') {
                $access = $this->accessServices->getClosestAccess($userToken->user_id, $congressId);
                if (!$access)
                    return response()->json(['message' => 'no access found '], 400);
            }
            $data = [
                'title' => $event,
                'body' => $event == 'collect' ?
                    '/room/' . $congressId :
                    '/room/' . $congressId . '/access/' . $access->access_id,
                'link' => $event == 'collect' ?
                    UrlUtils::getBaseUrlFrontOffice() . '/room/' . $congressId :
                    UrlUtils::getBaseUrlFrontOffice() . '/room/' . $congressId . '/access/' . $access->access_id
            ];

            $this->notificationService->sendNotification($data, [$userToken->firebase_key_user], false);
        }


    }

    public function getItemsEvaluation($congress_id)
    {
        if (!$congress = $this->congressServices->getById($congress_id)) {
            return response()->json('no congress found', 404);
        }
        return response()->json($this->congressServices->getItemsEvaluation($congress_id), 200);
    }

    public function addItemsEvaluation($congress_id, Request $request)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$request->has('grids') || sizeof($request->input('grids')) === 0) {
            return response()->json('field missing', 404);
        }
        $itemsEvaluation = $this->congressServices->getItemsEvaluation($congress_id);
        if (sizeof($itemsEvaluation) > 0) {
            foreach ($itemsEvaluation as $itemEvaluation) {
                if (sizeof($itemEvaluation->itemNote) > 0) {
                    return response()->json('You have already configured your grids', 404);
                }
            }
            foreach ($itemsEvaluation as $itemEvaluation) {
                $itemEvaluation->delete();
            }
        }

        $sumPonderation = 0;
        foreach ($request->input('grids') as $itemEvaluation) {
            $sumPonderation += $itemEvaluation['ponderation'];
        }
        if ($sumPonderation != 100) {
            return response()->json('sum ponderations must be 100', 404);
        }
        $this->congressServices->addItemsEvaluation($request->input('grids'), $congress_id);

        return response()->json('evaluation items added', 200);

    }

    public function addItemsNote($congress_id, $evaluation_inscription_id, Request $request)
    {

        if (!$congress = $this->congressServices->getById($congress_id)) {
            return response()->json('no congress found', 404);
        }
        if (!$evaluation = $this->adminServices->getEvaluationInscriptionByIdAndCongressId($evaluation_inscription_id, $congress_id)) {
            return response()->json('no evaluation found', 404);
        }
        $admin = $this->adminServices->retrieveAdminFromToken();
        if (!$admin || $admin->admin_id !== $evaluation->admin_id) {
            return response()->json('you have no rights', 404);
        }
        if (!$request->has('itemsNote')) {
            return response()->json('filed is missing', 404);
        }

        $itemEvaluations = $this->congressServices->getItemsEvaluation($congress_id);
        if (!$itemEvaluations || sizeof($itemEvaluations) !== sizeof($request->input('itemsNote'))) {
            return response()->json('problem !', 404);
        }
        $this->congressServices->addItemsNote(
            $request->input('itemsNote'),
            $evaluation_inscription_id
        );
        $note = 0;
        $itemsNote = $request->input('itemsNote');
        for ($i = 0; $i < sizeof($itemsNote); $i++) {
            if ($itemsNote[$i]['note'] < 0 || $itemsNote[$i]['note'] > 20) {
                return response()->json('the ' . ($i + 1) . ' item has a wrong mark', 400);
            }
            $note = $note + ($itemsNote[$i]['note'] * $itemEvaluations[$i]->ponderation);
        }
        $note = $note / 100;
        $evaluation->note = $note;
        $evaluation->commentaire = $request->input('globalComment');
        $evaluation->update();
        $user_congress = $this->userServices->getUserCongress($congress_id, $evaluation->user_id);
        $avg_note = $this->userServices->getAverageNote($evaluation->user_id, $congress_id);
        $user_congress->globale_score = $avg_note;
        $user_congress->update();
        return response()->json('items note affected', 200);


    }

    public function editConfigCongress(Request $request, $congressId)
    {

        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $configCongress = $this->congressServices->getCongressConfigById($congressId);

        $configLocation = $this->congressServices->getConfigLocationByCongressId($congressId);

        $configSubmission = $this->congressServices->getCongressConfigSubmissionById($congressId);

        $newConfig = $request->input("congress");

        $token = null;

        if ($newConfig['is_online']) {
            $token = $this->roomServices->createToken(
                $loggedadmin->email,
                'eventizer_room_' . $congressId,
                true,
                $loggedadmin->name
            );
        }
        $reservedMeetingTables = $this->meetingServices->countUsedMeetingTablesByCongressId($congressId);
        if ($reservedMeetingTables  > $request->input("congress")['nb_meeting_table']) {
            return response()->json(['error' => 'Insufficient tables'], 405);
        }
        $configCongress = $this->congressServices->editConfigCongress($configCongress, $request->input("congress"), $congressId, $token);
        if ($request->input("congress")['nb_meeting_table'] != 0) {
            $meetingtables = $this->meetingServices->deleteMeetingTablesWithNoMeeting($congressId);
            for ($i = 1; $i <= $request->input("congress")['nb_meeting_table']; $i++) {
                $label = "Table " . $i;
                $MeetTable = $this->meetingServices->addMeetingTable($label, $congressId);
            }
            if (count($meetingtables) != 0) {
            foreach ($meetingtables as $table) {
                $meetingtables = $this->meetingServices->removeDuplicatesMeetingTable($table->label, $congressId);
                }
            }
        }
        $submissionData = $request->input("submission");
        $theme_ids = $request->input("themes_id_selected");

        if (sizeof($submissionData) > 1) {
            $this->congressServices->addCongressSubmission(
                $configSubmission,
                $submissionData,
                $congressId
            );
        } else if($configSubmission = $this->congressServices->getConfigSubmission($congressId)) {
            $this->congressServices->deleteConfigsubmission($configSubmission);
            $this->congressServices->deleteAllThemes($congressId);
        }
        if ($theme_ids && sizeof($submissionData) > 0) {
            $this->congressServices->addSubmissionThemeCongress(
                $theme_ids,
                $congressId);
        }

        // Config Location
        $eventLocation = $request->input("eventLocation");

        if ($eventLocation && $eventLocation['countryCode'] && $eventLocation['cityName']) {

            $city = $this->geoServices->getCity($eventLocation['countryCode'], $eventLocation['cityName']);

            $this->congressServices->editCongressLocation($configLocation, $eventLocation, $city->city_id, $congressId);
        }

        // Config OnlineAccess Allowed
        $this->congressServices->deleteAllAllowedAccessByCongressId($congressId);
        $this->congressServices->addAllAllowedAccessByCongressId($request->input("congress")['privileges'], $congressId);

        return response()->json(['message' => 'edit configs success', 'config_congress' => $configCongress]);

    }

    public function addPaymentToUser($root, $user, $congress, $price)
    {
        $link = $root . "/api/users/" . $user->user_id . '/congress/' . $congress->congress_id . '/validate/' . $user->verification_code;
        $userPayment = $this->paymentServices->affectPaymentToUser($user->user_id, $congress->congress_id, $price, false);

        if ($mailtype = $this->congressServices->getMailType('inscription')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . '/room/'.$congress->congress_id.'/event-room' ;
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user,  $link, null, $userPayment ,null,null,null,null,null,null,null,null,null,[],null,null,$linkPrincipalRoom), $user, $congress, $mail->object, false, $userMail);
            }
        }
    }

    public function editCongress(Request $request, $congressId)
    {
        if (!$request->has(['name', 'start_date']))
            return response()->json(['message' => 'bad request'], 400);
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["message" => "congress not found"], 404);
        }

        if (!$config = ConfigCongress::where('congress_id', '=', $congressId)->first())
            $config = new ConfigCongress();

        if (!$config_selection = $this->congressServices->getConfigSelection($congressId))
            $config_selection = new ConfigSelection();

        $congress = $this->congressServices->editCongress($congress, $config, $config_selection, $request);
        return response()->json($congress);
    }

    public function getMinimalCongressById($congressId)
    {
        if (!$congress = $this->congressServices->getMinimalCongressById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }

        $congress = $this->congressServices->updateWithParticipantsCount($congress);

        return response()->json($congress);
    }

    public function getMinCongressData()
    {
        return $this->congressServices->getMinCongressData();
    }

    public function getCongressPagination(Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 6);
        $search = $request->query('search', '');
        $startDate = $request->query('startDate', '');
        $endDate = $request->query('endDate', '');
        $status = $request->query('status', '');
        $minPrice = $request->query('minPrice', '');
        $maxPrice = $request->query('maxPrice', '');
        $type = $request->query('type', '');


        $cacheKey = config('cachedKeys.EventPagination') . $page . $perPage . $search . $startDate . $endDate . $status.$minPrice.$maxPrice.$type;

        if (Cache::has($cacheKey)) {
            $events = Cache::get($cacheKey);
        } else {
            $events = $this->congressServices->getCongressPagination($page, $perPage, $search, $startDate, $endDate, $status,$minPrice,$maxPrice,$type);
            Cache::put($cacheKey, $events, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;
        }

        return $events;
    }

    public function getMinimalCongress()
    {
        return $this->congressServices->getMinimalCongress();
    }

    public function getCongressByIdBadge($congressId)
    {
        if (!$congress = $this->congressServices->getCongressByIdAndRelations($congressId, [
            'badges',
            'accesss' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('with_attestation', '=', 1);
            },
            'accesss.attestations'
        ])) {
            return response()->json(["error" => "congress not found"], 404);
        }

        return response()->json($congress);
    }


    public function getCongressById($congress_id)
    {
        ini_set('memory_limit', '-1');
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        return response()->json($congress);
    }

    public function getCongressDetailsById($congress_id)
    {
        $cacheKey = config('cachedKeys.Congress') . $congress_id;

        if (Cache::has($cacheKey)) {
            $congress = Cache::get($cacheKey);
        } else {
            $congress = $this->congressServices->getCongressDetailsById($congress_id);
            if (!$congress) {
                return response()->json(["error" => "congress not found"], 404);
            }
            Cache::put($cacheKey, $congress, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;
        }

        return response()->json($congress);
    }

    public function getCongressConfigById($congress_id)
    {
        if (!$configCongress = $this->congressServices->getCongressConfigById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $configSubmission = $this->congressServices->getCongressConfigSubmissionById($congress_id);
        $location = $this->geoServices->getCongressLocationByCongressId($congress_id);
        $allowedOnlineAccess = $this->congressServices->getAllAllowedOnlineAccess($congress_id);
        return response()->json([$configCongress, $location, $configSubmission, $allowedOnlineAccess]);
    }


    public function getStatsChartByCongressId($congressId)
    {
        $congress = $this->congressServices->getCongressByIdAndRelations($congressId, [
            'accesss' => function ($query) use ($congressId) {
                $query->where('show_in_register', '=', 1);
            },
            'users.responses.form_input',
            'users.responses.values',
            "users" => function ($query) use ($congressId) {
                $query->where('privilege_id', '=', config('privilege.Participant'));
            },
            "form_inputs.type",
            "form_inputs.values",
            "accesss.participants.user_congresses" => function ($query) {
                $query->where('privilege_id', '=', config('privilege.Participant'));
            },
            "tracking",
            "stand"
        ]);

        return response()->json($congress);

    }


    /**
     * @SWG\Get(
     *   path="/mobile/congress",
     *   tags={"Mobile"},
     *   summary="getCongressByAdmin",
     *   operationId="getCongressByAdmin",
     *   security={
     *     {"Bearer": {}}
     *   },
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function getCongressByAdmin()
    {
        $admin = $this->adminServices->retrieveAdminFromToken();
        return response()->json($this->congressServices->getCongressByAdmin($admin->admin_id));
    }

    public function sendMailAllParticipants($congressId)
    {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $mailtype = $this->congressServices->getMailType('confirmation');
        if ($mailtype) {
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);
            $mailId = $mail->mail_id;
            $users = $this->userServices->getUsersWithRelations($congressId,
                ['accesses' => function ($query) use ($congressId) {
                    $query->where("congress_id", "=", $congressId);
                }, 'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }, 'payments' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                },
                    'user_mails' => function ($query) use ($mailId) {
                        $query->where('mail_id', '=', $mailId);
                    }], null);
            foreach ($users as $user) {
                if (Utils::isValidSendMail($congress, $user)) {
                    $badge = $this->congressServices->getBadgeByPrivilegeId($congress,
                        $user->user_congresses[0]->privilege_id);
                    $badgeIdGenerator = $badge['badge_id_generator'];

                    $fileAttached = false;
                    $fileName = "badge.png";
                    if ($badgeIdGenerator != null) {
                        $fileAttached = $this->sharedServices->saveBadgeInPublic($badge,
                            $user,
                            $user->qr_code,
                            $user->user_congresses[0]->privilege_id,
                            $congress->congress_id);
                    }

                    $userMail = null;
                    if (sizeof($user->user_mails) == 0) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    } else {
                        $userMail = $user->user_mails[0];
                    }
                    if (Utils::isValidStatus($userMail)) {
                        $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . "/login";
                        $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . "/room/".$congressId.'/event-room';
                        $this->mailServices->sendMail($this->congressServices
                            ->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice,null,null,null,null,null,null,null,[],null,null,$linkPrincipalRoom),
                            $user, $congress, $mail->object, $fileAttached, $userMail, null, $fileName);
                    }
                }
            }
            return response()->json(['message' => 'send mail successs']);
        } else {
            return response()->json(['error' => 'vous devez configurer votre mail de confirmation'], 500);
        }
    }

    public function getAttestationDiversByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        return response()->json($this->badgeServices->getAttestationDiversByCongress($congressId));
    }

    private function isAllowedEdit($congress_id)
    {
        $users = User::where('congress_id', '=', $congress_id)
            ->get();

        return sizeof($users) == 0;
    }

    public function sendMailAllParticipantsSondage($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $mailtype = $this->congressServices->getMailType('sondage');
        $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);
        $mailId = $mail->mail_id;
        $users = $this->userServices->getUsersWithRelations($congressId,
            ['accesses' => function ($query) use ($congressId) {
                $query->where("congress_id", "=", $congressId);
                $query->where('with_attestation', "=", 1);
            },
                'payments' => function ($query) use ($congressId) {
                    $query->where("congress_id", "=", $congressId);
                }, 'user_congresses' => function ($query) use ($congressId) {
                $query->where("congress_id", "=", $congressId);
                $query->where('isPresent', '=', 1);
            },
                'user_mails' => function ($query) use ($mailId) {
                    $query->where('mail_id', '=', $mailId);
                }], 1);
        foreach ($users as $user) {
            if (Utils::isValidSendMail($congress, $user)) {
                if ($mail) {
                    $userMail = null;
                    if (sizeof($user->user_mails) == 0) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    } else {
                        $userMail = $user->user_mails[0];
                    }
                    if (Utils::isValidStatus($userMail)) {
                        $linkSondage = UrlUtils::getBaseUrl() . "/users/" . $user->user_id . '/congress/' . $congressId . '/sondage';
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, $linkSondage),
                            $user, $congress, $mail->object, false, $userMail);
                    }
                }
            }
        }
        return response()->json(['message' => 'send mail successs']);

    }

    public function sendMailAllParticipantsAttestation($congressId, $strict = 1)
    {

        // $strict = 0;
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $mailtype = $this->congressServices->getMailType('attestation');
        $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);
        $mailId = $mail->mail_id;
        $users = $this->userServices->getUsersWithRelations($congressId,
            ['accesses' => function ($query) use ($congressId) {
                $query->where("congress_id", "=", $congressId);
                $query->where('with_attestation', "=", 1);
            }, 'user_congresses' => function ($query) use ($congressId) {
                $query->where('isPresent', '=', 1);
            },
                'user_mails' => function ($query) use ($mailId) {
                    $query->where('mail_id', '=', $mailId);
                }], 1);
        foreach ($users as $user) {
            $request = array();
            if ($user->email != null && $user->email != "-" && $user->email != "" && sizeof($user->user_congresses) > 0) {
                if ($congress->attestation) {
                    array_push($request,
                        array(
                            'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false
                        ));
                }

                foreach ($user->accesses as $access) {
                    if ($strict == 0 || $access->pivot->isPresent == 1) {
                        if (sizeof($access->attestations) > 0) {
                            $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, config('privilege.Participant'));
                            if ($attestationId) {
                                array_push($request,
                                    array(
                                        'badgeIdGenerator' => $attestationId,
                                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                                        'qrCode' => false
                                    ));
                            }
                        }

                    }
                    //TODO Change performance
                    $chairPerson = $this->accessServices->getChairAccessByAccessAndUser($access->access_id, $user->user_id);
                    $privilegeId = null;
                    if ($chairPerson) {
                        $privilegeId = config('privilege.Moderateur');
                    }
                    $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($access->access_id, $user->user_id);
                    if ($speakerPerson) {
                        $privilegeId = config('privilege.Conferencier_Orateur');
                    }
                    $attestationId = null;
                    if ($privilegeId)
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, $privilegeId);
                    if ($attestationId) {
                        array_push($request,
                            array(
                                'badgeIdGenerator' => $attestationId,
                                'name' => Utils::getFullName($user->first_name, $user->last_name),
                                'qrCode' => false
                            ));
                    }

                }
                if ($mail) {
                    $userMail = null;
                    if (sizeof($user->user_mails) == 0) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    } else {
                        $userMail = $user->user_mails[0];
                    }
                    if (Utils::isValidStatus($userMail)) {
                        $fileName = 'attestations.zip';
                        $this->badgeServices->saveAttestationsInPublic($request);
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null),
                            $user, $congress, $mail->object, true, $userMail, null, $fileName);
                    }
                }
            }
        }
        return response()->json(['message' => 'send mail successs']);
    }


    public function getAllCongresses()
    {
        return $this->congressServices->getAllCongresses();
    }

    public function sendCustomMailToAllUsers($mail_id, Request $request)
    {

        if (!$mail = $this->congressServices->getEmailById($mail_id))
            return response()->json(['response' => 'mail not found'], 404);
        $congressId = $mail->congress_id;
        $mailId = $mail->mail_id;
        $congress = $this->congressServices->getCongressById($mail->congress_id);
        $privilege_ids = $request->input('privilege_ids');

        $users = $this->userServices->getUsersWithRelations($congressId,
            [
                'accesses' => function ($query) use ($congressId) {
                    $query->where("congress_id", "=", $congressId);
                },
                'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                },
                'payments' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                },
                'user_mails' => function ($query) use ($mailId) {
                    $query->where('mail_id', '=', $mailId);
                }
            ], null, $privilege_ids);


        foreach ($users as $user) {
            if (Utils::isValidSendMail($congress, $user)) {
                $userMail = null;
                if (sizeof($user->user_mails) == 0) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                } else {
                    $userMail = $user->user_mails[0];
                }

                if (Utils::isValidStatus($userMail)) {
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null)
                        , $user, $congress, $mail->object, false, $userMail);
                }
            }
        }

        return response()->json(["status" => "success"], 200);
    }

    public function setProgramLink(Request $request, $congress_id)
    {
        if (!$request->has("programLink")) return response()->json(['error' => 'invalid request'], 400);
        if (!$congress = $this->congressServices->getCongressById($congress_id))
            return response()->json(['error' => 'congress not found'], 402);
        $congress->program_link = $request->input('programLink');
        $congress->update();
        return $congress;
    }

    function getParticipantsCounts(Request $request)
    {
        $result = [];
        foreach ($request->all() as $congress_id) {
            if (!$this->congressServices->getCongressById($congress_id))
                return response()->json(['message' => 'congresses not found'], 404);
            array_push($result, (object)[
                'congress_id' => $congress_id,
                'count' => $this->congressServices->getParticipantsCount($congress_id, 3, null)
            ]);
        }
        return $result;
    }

    function getLogo($congress_id)
    {
        if (!$config = $this->congressServices->getCongressConfig($congress_id)) return response()->json(['response' => 'congress not found'], 404);
        if (!$config->logo) return response()->json(['response' => 'no logo'], 400);
        return Storage::download($config->logo);
    }

    function getBanner($congress_id)
    {
        if (!$config = $this->congressServices->getCongressConfig($congress_id)) return response()->json(['response' => 'congress not found'], 404);
        if (!$config->banner) return response()->json(['response' => 'no logo'], 400);
        return Storage::download($config->banner);
    }

    function addDemo($admin_id)
    {
        // add congrees , congress_config , Admin_Congress
        $congress = $this->congressServices->addCongress("DemoCongress", date('Y-m-d'), date('Y-m-d'), 99, 1,
            true, false, true, "this is the Demo descrtiption", $admin_id);

        // add users
        for ($x = 0; $x <= 10; $x++) {
            $arr = array('email' => 'DemoMail' . $x . '@gmail.com', 'first_name' => 'FirstnameFrmo' . $x, 'last_name' => 'LastnameDemo' . $x,
                'gender' => 1, 'mobile' => 89456123, 'country_id' => 'TUN');
            $user = $this->userServices->addUser($arr);
            $userCongress = new UserCongress();
            $userCongress->congress_id = $congress->congress_id;
            $userCongress->user_id = $user->user_id;
            $userCongress->privilege_id = config('privilege.Participant');    //privilege particiapant
            $userCongress->save();
        }

        //add badges
        $badge = new Badge();
        $badge->badge_id_generator = "5c6dbd67d2cb3900015d7a65";
        $badge->privilege_id = config('privilege.Participant');
        $badge->congress_id = $congress->congress_id;
        $badge->save();

        //add access (add multiple access)
        for ($x = 0; $x <= 10; $x++) {
            $access = new Access();
            $access->name = "AccesDemo " . $x;
            $access->start_date = date('Y-m-d');
            $access->end_date = date('Y-m-d');
            $access->access_type_id = 1; // type session
            $access->price = 99;
            $access->packless = 1;
            $access->description = " description de l'access demo num " . $x;
            $access->seuil = 99;
            $access->max_places = 100;
            $access->congress_id = $congress->congress_id;
            $access->save();
        }
        //add mail
        $this->mailServices->saveMail($congress->congress_id, 1, "inscription", '<p>Veuillez cliquer sur ce lien afin de valider votre paiement.</p><p><a href="{{%24link}}">Lien</a></p>');
        $this->mailServices->saveMail($congress->congress_id, 2, "Paiement", "Veuillez cliquer sur ce lien afin de valider votre paiement");
        $this->mailServices->saveMail($congress->congress_id, 5, "Confirmation", "Voullez vous vraiment confirmer Cette action");

        return response()->json(["status" => "success added demo congress"], 200);
    }

    public function getStatsByCongressId($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json('no congress found', 404);
        }
        $totalUsers = $this->congressServices->getParticipantsCount($congressId, null, null);
        $participantUsers = $this->congressServices->getParticipantsCount($congressId, config('privilege.Participant'), null);
        $revenue = $this->congressServices->getRevenuCongress($congressId);
        $gratuitNb = $this->paymentServices->getFreeUserByCongressId($congressId);
        $totalPresenceUsers = $this->congressServices->getParticipantsCount($congressId, null, 1);
        $totalParPresenceUsers = $this->congressServices->getParticipantsCount($congressId, config('privilege.Participant'), 1);
        $users = $this->userServices->getUsersTracking($congressId, [1, 2, 3, 4], config('privilege.Participant'));
        $access = $this->accessServices->getAllAccessByCongress($congressId, null,
            [
                'participants.user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                    $query->where('privilege_id', '=', config('privilege.Participant'));
                },
                'participants.payments' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }
            ]);
        $stands = $this->standServices->getAllStandByCongressId($congressId);
        $usersData = $this->congressServices->getTimePassedInCongressAccessAndStand($users, $congress, $access, $stands);
        return response()->json([
            'total_users' => $totalUsers,
            'participant_users' => $participantUsers,
            'revenues' => $revenue,
            'total_free' => $gratuitNb,
            'total_presence_users' => $totalPresenceUsers,
            'total_presence_participants' => $totalParPresenceUsers,
            'usersData' => $usersData
            // 'timePassed' => $timePassed
        ]);


    }

    public function getStatsAccessByCongressId($congressId)
    {
        //Cette stats concerne les participants et les ateliers qui ont choisit.
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json('no congress found', 404);
        }

        $access = $this->accessServices->getAllAccessByCongress($congressId, null,
            [
                'participants.user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                    $query->where('privilege_id', '=', config('privilege.Participant'));
                },
                'participants.payments' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }
            ]);

        //  $access = $this->accessServices->getAccessPassedTime($access,$congress);

        return response()->json($access);


    }

    public function getUserCongress(Request $request)
    {
        $offset = $request->query('offset', 0);
        $perPage = $request->query('perPage', 6);
        $search = $request->query('search', '');
        $startDate = $request->query('startDate', '');
        $endDate = $request->query('endDate', '');
        $status = $request->query('status', '');
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }

        $events = $this->congressServices->getUserCongress($offset, $perPage, $search, $startDate, $endDate, $status, $user);
        return response()->json($events, 200);
    }

    public function confirmPresence($congress_id, $user_id, $present)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        if (!$user = $this->userServices->getUserById($user_id)) {
            return response()->json(['error' => 'user not found'], 404);
        }
        if (!$user_congress = $this->userServices->getUserCongress($congress_id, $user_id)) {
            return response()->json(['error' => 'user is not registered in congress'], 404);
        }
        if (!($adminCongress = (AdminCongress::where('congress_id', '=', $congress_id)
            ->where('privilege_id', '=', config('privilege.Admin'))->first()))) {
            return response()->json(['error' => 'bad request'], 400);
        }
        $user_congress = $this->congressServices->confirmPresence($congress_id, $user_id, $present);
        if (!$admin = $this->adminServices->getAdminById($adminCongress->admin_id)) {
            return response()->json(['error' => 'No admin found'], 404);
        }
        // print_r(json_encode($admin));
        if ($user_congress->will_be_present == 1) {
            $template = '<p>L\'utilisateur {{$participant-&gt;last_name}} {{$participant-&gt;first_name}} a accepté d\'être présent à votre événement {{$congress-&gt;name}}</p>';
        } else {
            $template = '<p>L\'utilisateur {{$participant-&gt;last_name}} {{$participant-&gt;first_name}} a refusé d\'être présent à votre événement {{$congress-&gt;name}}</p>';
        }
        $objectMail = 'Confirmation du présence';
        $this->mailServices->sendMail($this->congressServices->renderMail($template, $congress, $user, null, null, null), null, null, $objectMail, false, null, $admin->email);
        $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
        return redirect($linkFrontOffice);
    }

    /*
     * Get all users peacksource
     *  @congressId
     *
     *  @Response format
     *   - id
     *   - name
     *   - role
     *   - channel name (access name / stand name)
     *   - avatar id
     *   - authorized_channels (accceses)
     */
    public function getUsersByCongressPeacksource($congressId)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(['response' => 'congress not found'], 404);
        }
        $cacheKey = config('cachedKeys.PeacksourceUsers') . $congressId;
        
        if (Cache::has($cacheKey)) {
            $results = Cache::get($cacheKey);
        } else {
            $users = $users = $this->userServices->getUsersWithRelations($congressId,
                ['accesses' => function ($query) use ($congressId) {
                    $query->where("congress_id", "=", $congressId);
                }, 'user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }, 'organization' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }, 'organization.stands' => function ($query) use ($congressId) {
                    $query->where('Stand.congress_id', '=', $congressId);
                }, 'speaker_access' => function ($query) use ($congressId) {
                    $query->where('Access.congress_id', '=', $congressId);
                }, 'chair_access' => function ($query) use ($congressId) {
                    $query->where('Access.congress_id', '=', $congressId);
                }, 'profile_img'], null);
            
            $results = $this->userServices->mappingPeacksourceData($congress, $users);

            Cache::put($cacheKey, $results, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;
        }

        return response()->json($results);
    }

    public function getListTrackingByCongress($congressId, Request $request)
    {
        return response()->json($this->trackingServices->getTrackings($congressId, $request));

    }

    public function setCurrentParticipants($congressId, Request $request)
    {
        if (!$request->has('nbParticipants')) {
            return response()->json(['response' => 'bad request: missing nbParticipants'], 400);
        }
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(['response' => 'congress not found'], 404);
        }
        $accessId = $request->input('accessId');

        if (!$accessId) {
            $this->congressServices->setCurrentParticipants($congressId, $request->input('nbParticipants'));
        } else {
            $this->accessServices->setCurrentParticipants($accessId, $request->input('nbParticipants'));
        }

        return response()->json(['message' => 'current participant number set success'], 200);
    }

    public function affectAbstractBookPathToCongress(Request $request , $congressId)
    {
        $savedPath = $request->input('path');
        $congress = $this->congressServices->getById($congressId);
        $congress->path_abstract_book = $savedPath;
        $congress->update();
         return response()->json(['path' => $savedPath]); 
    }

    public function affectLogoToCongress(Request $request, $congressId) {
        $path = $request->input('path');
        $config_congress = $this->congressServices->getCongressConfigById($congressId);
        $config_congress->logo = $path;
        $config_congress->update();
        return response()->json(['message' => 'success']); 
    }

    public function getConfigLandingPage($congress_id)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $config_landing_page = $this->congressServices->getConfigLandingPageById($congress_id);
        $configLocation = $this->congressServices->getConfigLocationByCongressId($congress_id);
        return response()->json(['config_landing_page' => $config_landing_page, 'configLocation' => $configLocation], 200);
    }

    public function getGenericFmenus($congress_id)
    {
        $FMenu = $this->congressServices->getGenericFmenus($congress_id);
        return response()->json($FMenu, 200);
    }

    public function editFmenus($congress_id, Request $request)
    {
        if (!$loggedadmin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $fmenus = $request->all();

        if ($fmenus) {
            foreach ($fmenus as $fmenu) {
                if ($fetched = $this->fmenuServices->getFMenuById($fmenu['FMenu_id'], $congress_id)) {
                    $fmenu = $this->fmenuServices->editFMenu($fmenu, $congress_id, $fetched);
                } else {
                    $fmenu = $this->fmenuServices->editFMenu($fmenu, $congress_id);
                }
            }
            $fmenus = $this->congressServices->getGenericFmenus($congress_id);
        }

        return response()->json($fmenus, 200);
    }

    public function editConfigLandingPage($congress_id, Request $request)
    {
        if (!$this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }

        $config_landing_page = $this->congressServices->getConfigLandingPageById($congress_id);
        $config_landing_page = $this->congressServices->editConfigLandingPage($config_landing_page, $request, $congress_id);

        $configLocation = $this->congressServices->getConfigLocationByCongressId($congress_id);
        // Config Location
        $eventLocation = $request->input("eventLocation");
        
        if ($eventLocation && $eventLocation['countryCode'] && $eventLocation['cityName']) {

            $city = $this->geoServices->getCity($eventLocation['countryCode'], $eventLocation['cityName']);

            $this->congressServices->editCongressLocation($configLocation, $eventLocation, $city->city_id, $congress_id);
        }

        return response()->json(['config_landing_page' => $config_landing_page,  'configLocation' => $configLocation], 200);
    }

    public function addLandingPageSpeaker($congress_id, Request $request)
    {
        if (!$request->has(['first_name', 'last_name', 'role']))
            return response()->json(['message' => 'bad request:first_name,last_name and role are required '], 400);
        if (!$this->adminServices->retrieveAdminFromToken())
            return response()->json(['error' => 'admin_not_found'], 404);
        
            $lp_speaker = $this->congressServices->addLandingPageSpeaker($congress_id, $request);

            return response()->json($lp_speaker, 200);
    }

    public function getLandingPageSpeakers($congress_id)
    {
        if (!$this->adminServices->retrieveAdminFromToken())
            return response()->json(['error' => 'admin_not_found'], 404);
        
        $speakers = $this->congressServices->getLandingPageSpeakers($congress_id);
        return response()->json($speakers, 200);
    }

    public function editLandingPageSpeaker($lp_speaker_id, Request $request)
    {
        if (!$this->adminServices->retrieveAdminFromToken())
            return response()->json(['error' => 'admin_not_found'], 404);

        if (!$speaker = $this->congressServices->getLandingPageSpeakerById($lp_speaker_id))
            return response()->json(['error' => 'Speaker not found'], 404);

        $speaker = $this->congressServices->editLandingPageSpeaker($speaker, $request);
        return response()->json($speaker, 200);
    }

    public function deleteLandingPageSpeaker($lp_speaker_id)
    {
        if (!$this->adminServices->retrieveAdminFromToken())
            return response()->json(['error' => 'admin_not_found'], 404);

        if (!$speaker = $this->congressServices->getLandingPageSpeakerById($lp_speaker_id))
            return response()->json(['error' => 'Speaker not found'], 404);

        $this->congressServices->deleteLandingPageSpeaker($speaker);
        return response()->json(['Deleted succesfully'], 200);

    }

    public function syncronizeLandingPage($congress_id)
    {
        if (!$this->adminServices->retrieveAdminFromToken())
            return response()->json(['error' => 'admin_not_found'], 404);

        if (!$congress = $this->congressServices->getCongressById($congress_id)) 
            return response()->json(["message" => "congress not found"], 404);
        
        if (!$config_congress = $this->congressServices->getCongressConfigById($congress_id))
                return response()->json(["error" => "congress not found"], 404);
        
        $config_landing_page = $this->congressServices->syncronizeLandingPage($congress_id, $congress,$config_congress, $this->congressServices->getConfigLandingPageById($congress_id));
        $configLocation = $this->congressServices->getConfigLocationByCongressId($congress_id);
        return response()->json(['config_landing_page' => $config_landing_page, 'configLocation' => $configLocation], 200);
    }
    public function getConfigLandingPageToFrontOffice($congress_id)
    {
        $config_landing_page = $this->congressServices->getConfigLandingPageById($congress_id);
        $configLocation = $this->congressServices->getConfigLocationByCongressId($congress_id);
        return response()->json(['config_landing_page' => $config_landing_page, 'configLocation' => $configLocation], 200);
    }
    public function getLandingPageSpeakersToFrontOffice($congress_id)
    {
        $speakers = $this->congressServices->getLandingPageSpeakers($congress_id);
        return response()->json($speakers, 200);
    }

    public function getNumberOfParticipants($congress_id)
    {
        if (!$congress = $this->congressServices->getCongressById($congress_id)) 
            return response()->json(["message" => "congress not found"], 404);
        
        $participants = $this->congressServices->getParticipantsCachedCount($congress_id);
        return response()->json($participants, 200);
    }

}