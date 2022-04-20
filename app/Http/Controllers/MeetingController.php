<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MeetingServices;
use App\Services\UserServices;
use App\Services\AdminServices;
use App\Services\MailServices;
use App\Services\CongressServices;
use Illuminate\Support\Str;
use App\Services\UrlUtils;
use App\Services\Utils;
use DateTime;
use Illuminate\Support\Facades\Log;



class MeetingController extends Controller
{
  protected $congressServices;
  protected $adminServices;
  protected $meetingServices;
  protected $userServices;
  protected $mailServices;

  function __construct(
    CongressServices $congressServices,
    AdminServices $adminServices,
    MeetingServices $meetingServices,
    MailServices $mailServices,
    UserServices $userServices
  ) {
    $this->congressServices = $congressServices;
    $this->adminServices = $adminServices;
    $this->meetingServices = $meetingServices;
    $this->mailServices = $mailServices;
    $this->userServices = $userServices;
  }


  public function getUserMeetingById($congress_id, Request $request)
  {
    $status = $request->query("status", '');
    return $this->meetingServices->getMeetingByUserId($request->input('user_id'), $congress_id, $status);
  }

  
  function addMeeting(Request $request)
  {
    $congress = $this->congressServices->getCongressDetailsById($request->input('congress_id'));
    $userConnected = $this->userServices->retrieveUserFromToken();
    $user_receiver = $this->userServices->getUserMinByCongress($request->input('user_received_id'), $request->input('congress_id'));
    if (!$request->has('start_date')) {
      return response()->json(['response' => 'Meeting date not found'], 401);
    }
    $meeting_date = $request->input('start_date');
    if (!$userConnected) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $user_sender = $this->userServices->getUserMinByCongress($userConnected->user_id, $request->input('congress_id'));
    if (!$user_receiver) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $duplicated_meeting = $this->meetingServices->countMeetingsByUserOnDate($congress->congress_id, $meeting_date, $user_sender->user_id);
    if ($duplicated_meeting > 0) {
      return response()->json(['response' => 'Meeting on the same date found'], 401);
    }
    $user_receiver->meeting_code = Str::random(40);
    $user_receiver->save();
    $meeting = null;
    $userMeet = null;
    if ($request->has('meeting_id')) {
      $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
      $userMeet = $this->meetingServices->getFirstUserMeetingsByMeetingId($meeting->meeting_id);
    }
    $meeting = $this->meetingServices->addMeeting($meeting,  $request);
    $userMeeting = $request->has('meeting_id') ? $this->meetingServices->editUserMeeting($userMeet) : $this->meetingServices->addUserMeeting($meeting, $request->input('user_received_id'), $user_sender->user_id);
    if ($mailtype = $this->congressServices->getMailType('request_meeting')) {
      if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender, $user_receiver->meeting_code), $user_receiver, $congress, $mail->object, null, $userMail, null, null);
      } else {
        if ($mail = $this->congressServices->getMailOutOfCongress(24)) {
          $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
          $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender, $user_receiver->meeting_code), $user_receiver, $congress, $mail->object, null, $userMail, null, null);
        }
      }
    }
    if ($request->has('verification_code')) {
      $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
      return redirect($linkFrontOffice);
    }
    return response()->json($meeting, 200);
  }

  function modiyStatus($meetingId, Request $request)
  {
    if (!$meetingId) {
      return response()->json(['required value' => ['meeting_id']], 400);
    }
    if (!$request->has('status')) {
      return response()->json(['required value' => ['status']], 400);
    }
    $status = $request->input('status');
    $meeting = null;
    if (!$meeting = $this->meetingServices->getMeetingById($meetingId)) {
      return response()->json(['response' => 'Meeting not found'], 401);
    }
    $user_meeting = $meeting['user_meeting']->first();
    $congressId = $meeting->congress_id;
    if (!$congress = $this->congressServices->getCongressDetailsById($congressId)) {
      return response()->json(["message" => "congress not found"], 404);
    }
    $nb_meeting_tables = $congress['config']['nb_meeting_table'];
    $userConnected = $this->userServices->retrieveUserFromToken();
    $user_receiver = $this->userServices->getUserMinByCongress($user_meeting->user_receiver_id, $congressId);
    if (!$userConnected) {
      if ($user_receiver) {
        if ($request->has('verification_code')) {
          $verification_code = $request->input('verification_code');
          if (!$user_receiver->meeting_code == $verification_code) {
            return response()->json(['response' => 'No verification code found'], 401);
          }
        }
      }
    }
    if (!$user_receiver) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $user_meeting = $meeting['user_meeting']->first();
    $user_sender = $this->userServices->getUserMinByCongress($user_meeting->user_sender_id, $congressId);
    if (!$user_sender) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $meeting = $this->handleModifyMeetingStatus($status, $congressId, $user_receiver, $user_sender, $request, $nb_meeting_tables, $meeting, $user_meeting, $congress);
    if ($request->has('verification_code')) {
      $linkFrontOffice = UrlUtils::getUserMeetingLinkFrontoffice($congressId);
      return redirect($linkFrontOffice);
    }
    return response()->json($meeting, 200);
  }

  public function sendDeclineMailToUserSender($congress, $mailtype, $user_sender, $meeting, $user_receiver)
  {
    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
      $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
      $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, $userMail, null, null);
    } else {
      if ($mail = $this->congressServices->getMailOutOfCongress($mailtype->mail_type_id)) {
        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, $userMail, null, null);
      }
    }
  }

  public function affectTablesToMeeting($meeting, $user_meeting, $congressId, $request)
  {
    $date =  $meeting->start_date;
    $meetingtable = $this->meetingServices->getAvailableMeetingTable($date, $congressId);
    if ($meetingtable) {
      $meeting = $this->meetingServices->addTableToMeeting($meeting, $meetingtable->meeting_table_id);
    } else {
      $status = -1;
      $user_meeting = $this->meetingServices->updateMeetingStatus($user_meeting, $request, $status);
      $meeting = $this->meetingServices->removeTableFromMeeting($meeting);
      return response()->json(['error' => 'Insufficient tables'], 405);
    }
  }

  function addMeetingEvaluation(Request $request)
  {
    $user = $this->userServices->retrieveUserFromToken();
    if (!$user) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $meetingEvaluation = $this->meetingServices->addMeetingEvaluation($request, $user->user_id);

    return response()->json($meetingEvaluation, 200);
  }

  public function declineConflictsMeetings($conflicts, $user_meeting, $congress, $user_receiver)
  {
    $mailtype = $this->congressServices->getMailType('decline_meeting');
    foreach ($conflicts as $conflict_meeting) {
      $conflict_meeting = $this->meetingServices->declineMeeting($conflict_meeting['user_meeting']->first());
      $user_sender_conflict = $this->userServices->getUserMinByCongress($user_meeting->user_sender_id, $congress->congress_id);
      $this->sendDeclineMailToUserSender($congress, $mailtype, $user_sender_conflict, $conflict_meeting, $user_receiver);
    }
  }

  public function sendAnnulationMail($congress, $mailtype, $user_sender, $meeting, $user_receiver)
  {
    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
      $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
    } else {
      if ($mail = $this->congressServices->getMailOutOfCongress(27)) {
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
      }
    }
  }

  public function sendAcceptMeetingsMail($congress, $user_sender, $meeting, $user_receiver)
  {
    $meeting = $this->meetingServices->getMeetingById($meeting->meeting_id);
    $meetingtable = $meeting['meetingtable'];
    if ($mailtype = $this->congressServices->getMailType('accept_meeting')) {
      if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender, null, $meetingtable['label']), $user_sender, $congress, $mail->object, null, $userMail, null, null);
      } else {
        if ($mail = $this->congressServices->getMailOutOfCongress(25)) {
          $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
          $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress,  $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender, null, $meetingtable['label']),  $user_sender, $congress, $mail->object, null, $userMail, null, null);
        }
      }
    }
  }

  public function makeOrganizerPresent($meeting_id, Request $request)
  {
    $meeting = $this->meetingServices->getMeetingById($meeting_id);
    $this->meetingServices->makeOrganizerPresent($meeting, $request->input('is_organizer_present'));
  }

  public function makeParticipantPresent($meeting_id, Request $request)
  {
    $user_meeting = $this->meetingServices->getUserMeetingsById($meeting_id, $request->input('user_id'));
    $this->meetingServices->makeParticipantPresent($user_meeting, $request->input('is_participant_present'));
  }

  public function getTotalNumberOfMeetings($congress_id)
  {
    $totalNumber = $this->meetingServices->getTotalNumberOfMeetingsByCongress($congress_id);
    $numberOfAcceptedMeetings = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congress_id, 1);
    $numberOfMeetingsDone = $this->meetingServices->getMeetingsDone($congress_id, 1, 1);
    $percentageOfAcceptedMeetings = $totalNumber > 0 ? ($numberOfAcceptedMeetings / $totalNumber) : 0 ;
    $percentageOfMeetingsDone = $totalNumber > 0 ? ($numberOfMeetingsDone / $totalNumber) : 0;
    return response()->json([
      "pourcentage_meetings_accpeted" => $percentageOfAcceptedMeetings, "pourcentage_meetings_done" => $percentageOfMeetingsDone
    ], 200);
  }

  public function getNumberOfMeetings($congress_id, Request $request)
  {
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json('no congress found', 404);
    }
    $startDate = $request->query('startDate', '');
    $endDate = $request->query('endDate', '');
    $congressStartDate = $startDate == '' ? new DateTime($congress->start_date) : new DateTime($startDate);
    $congressEndDate = $endDate == '' ? new DateTime($congress->end_date) : new DateTime($endDate);
    $interval = $congressEndDate->diff($congressStartDate);
    $days = $interval->format('%a');
    $nombres = array();

    for ($i = 0; $i <=  $days; $i++) {

      $nombre_meetings_accpeted = $this->meetingServices->getNumberOfMeetings($congress_id, 1, date('Y-m-d', strtotime($congress->start_date . ' +' . $i . 'days')));
      $nombre_meetings_Refused = $this->meetingServices->getNumberOfMeetings($congress_id, -1, date('Y-m-d', strtotime($congress->start_date . ' +' . $i . 'days')));
      $nombre_meetings_waiting = $this->meetingServices->getNumberOfMeetings($congress_id, 0, date('Y-m-d', strtotime($congress->start_date . ' +' . $i . 'days')));
      array_push($nombres, [
          "type" => "val3",
          "date" => str_replace('-', '/', strval(date('Y-m-d', strtotime($congress->start_date . ' +' . $i . 'days')))),
          "Alpha" => strval($nombre_meetings_accpeted), 
          "Delta" => strval($nombre_meetings_Refused),
          "Sigma" => strval($nombre_meetings_waiting)
      ]);
    }
    return response()->json($nombres, 200);
  }

  public function getRequestDetailsPagination($congress_id, Request $request)
  {
    $per_page = $request->query('perPage', 10);
    $startDate = $request->query('startDate', '');
    $endDate = $request->query('endDate', '');
    $search = $request->query('search', '');
    return $this->meetingServices->getRequestDetailsPagination($congress_id, $per_page, $startDate, $endDate, $search);

  }

  public function getAvailableTimeslots($congressId)
  {
    if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
      return response()->json('no admin found', 404);
    }
    if (!$congress = $this->congressServices->getCongressById($congressId)) {
      return response()->json('no congress found', 404);
    }
    // number of participants per event
    $numberOfParticipants = $this->congressServices->getParticipantsCount($congressId, null, null);
    // converting start and end time to 24 hours format
    $congressStartTime12HoursFormat = Utils::convertDateTimeToTime($congress->start_date);
    $congressStartTime24HoursFormat = Utils::convertTimeTo24HoursFormat($congressStartTime12HoursFormat);
    $congressEndTime12HoursFormat = Utils::convertDateTimeToTime($congress->end_date);
    $congressEndTime24HoursFormat = Utils::convertTimeTo24HoursFormat($congressEndTime12HoursFormat);
    $meetingDuration = $congress->config->meeting_duration;
    $pauseDuration = $congress->config->pause_duration;
    // getting the number of timeslots per user
    $timeslotPerUser = $this->meetingServices->getMeetingsTimes($congressStartTime24HoursFormat, $congressEndTime24HoursFormat, $meetingDuration, $pauseDuration);
    // calculating the total number of timeslots
    $totalTimeslots = $timeslotPerUser * $numberOfParticipants;
    // calculating the total number of reserved timeslots which is equal to number of uwer_meetings accepted (to know the number of participants) + the nuber of meetings accepted (to know the number of organizers)
    $meetingsNumber = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congressId, 1);
    $userMeetingsNumber = $this->meetingServices->getTotalNumberOfUserMeetingsBySatuts($congressId, 1);
    $reservedTimeslots = $meetingsNumber + $userMeetingsNumber;
    $availableTimeslots = $totalTimeslots - $reservedTimeslots;
    $averaveAvailableTimeslots = $totalTimeslots > 0 ? $availableTimeslots / $totalTimeslots : 0;

    // getting number of participants having at least one meeting
    $participantsHavingMeetings = $this->userServices->getNumberOfUsersHavingMeeting($congressId);
    $averageOfParticipantsHavingMeetings = $numberOfParticipants > 0 ? $participantsHavingMeetings / $numberOfParticipants : 0;
    return response()->json(['AvailableTimeslots' => $averaveAvailableTimeslots, 'ParticipantshavingMeetings' => $averageOfParticipantsHavingMeetings], 200);
  }

  public function getTotalNumberOfMeetingsWithSatuts($congressId, Request $request)
  {
    if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
      return response()->json('no admin found', 404);
    }
    $status = $request->query('status');
    if (!$status && $status != 0) {
      return response()->json('status required', 400);
    }
    if (!$congress = $this->congressServices->getCongressById($congressId)) {
      return response()->json('congress not found', 404);
    }
    $numberOfMeetings = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congressId, $status);
    return response()->json($numberOfMeetings, 200);
  }

  public function getMeetingsBetweenTwoDatesByStatus($congressId, Request $request)
  {
    if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
      return response()->json('no admin found', 404);
    }
    if (!$congress = $this->congressServices->getCongressById($congressId)) {
      return response()->json('congress not found', 404);
    }
    $startDate = $request->input('startDate');
    if (!$startDate) {
      return response()->json('start date required', 400);
    }
    $endDate = $request->input('endDate');
    if (!$endDate) {
      return response()->json('end date required', 400);
    }
      $acceptedMeetings = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congressId, 1, new DateTime($startDate), new DateTime($endDate));
      $rejectedMeetings = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congressId, -1, new DateTime($startDate), new DateTime($endDate));
      $waitingMeetings = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congressId, 0, new DateTime($startDate), new DateTime($endDate));
      $totalMeetings = $acceptedMeetings + $rejectedMeetings + $waitingMeetings;
      $response = [
        [
          "label" => "Accepted meetings",
          "value" => $acceptedMeetings
        ],
        [
          "label" => "Rejected meetings",
          "value" => $rejectedMeetings
        ],
        [
          "label" => "Pending meetings",
          "value" => $waitingMeetings
        ],
        [
          "label" => "Total meetings",
          "value" => $totalMeetings
        ],
      ];
      return response()->json($response, 200);
    }

  public function setFixTables(Request $request, $congress_id)
  {
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json(["message" => "congress not found"], 404);
    }
    $isSelected = null;
    if ($congress->congress_type_id < 3) {
      $isSelected = 1;
    }
    $errorTables = $this->meetingServices->setFixTables($request, $congress_id, $isSelected);
    $fixTables = $this->meetingServices->getFixTables($congress_id);
    /* comment set auto label fix table */
    /*
    $nbTableFix = $fixTables->count();
    $labelFixTables = $congress->config->label_fix_table != null ? $congress->config->label_fix_table : 'TF';

    if ($nbTableFix != 0) {
      $this->meetingServices->InsertFixTable($nbTableFix, $fixTables, $labelFixTables);
    }*/
    return response()->json(['fixTables' => $fixTables, 'errorTables' => $errorTables], 200);
  }


  public function getMeetingTableByCongress($congress_id, Request $request)
  {
    $perPage = $request->query('perPage', 10);
    $search = Str::lower($request->query('search', ''));
    $listMeetingTables = $this->meetingServices->getMeetingTableByCongress($congress_id, $perPage, $search);
    return response()->json($listMeetingTables, 200);
  }

  public function getMeetingPlanning($meeting_table_id)
  {
    return $this->meetingServices->getMeetingPlanning($meeting_table_id);
  }
  
  public function getFixTables($congress_id, Request $request)
  {
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json(['response' => 'Congress not found', 404]);
    }
    $perPage = $request->query('perPage', 10);
    $page = $request->query('page', 1);
    $search = $request->query('search', '');
    $fixTables = $this->meetingServices->getCachedFixTables($congress_id, $page, $perPage, $search);
    return response()->json($fixTables, 200);
  }

   function modifyStatusByOrganizer($meetingId, Request $request)
  {
    if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
      return response()->json('no admin found', 404);
    }
    if (!$meetingId) {
      return response()->json(['meeting required!'], 400);
    }
    if (!$request->has('status')) {
      return response()->json(['required value' => ['status']], 400);
    }
    $status = $request->input('status');
    if (!$meeting = $this->meetingServices->getMeetingById($meetingId)) {
      return response()->json(['response' => 'Meeting not found'], 404);
    }
    $user_meeting = $meeting['user_meeting']->first();
    $congressId = $meeting->congress_id;
    if (!$congress = $this->congressServices->getCongressDetailsById($congressId)) {
      return response()->json(["message" => "congress not found"], 404);
    }
    $nb_meeting_tables = $congress['config']['nb_meeting_table'];

    $user_meeting = $meeting['user_meeting']->first();
    $user_receiver = $this->userServices->getUserMinByCongress($user_meeting->user_receiver_id, $congressId);
    if (!$user_receiver) {
      return response()->json(['response' => 'receiver not found'], 401);
    }
    $user_sender = $this->userServices->getUserMinByCongress($user_meeting->user_sender_id, $congressId);
    if (!$user_sender) {
      return response()->json(['response' => 'sender not found'], 401);
    }

    $meeting = $this->handleModifyMeetingStatus($status, $congressId, $user_receiver, $user_sender, $request, $nb_meeting_tables, $meeting, $user_meeting, $congress);
    if ($mailtype = $this->congressServices->getMailType('decline_meeting')) {
      $this->sendDeclineMailToUserReciever($congress, $mailtype, $user_sender, $meeting, $user_receiver);
    }
    return response()->json($meeting, 200);
  }

  public function handleModifyMeetingStatus($status, $congressId, $user_receiver, $user_sender, $request, $nb_meeting_tables, $meeting, $user_meeting, $congress)
  {
    if ($status == 1) {
      $tableFixSender = $this->meetingServices->getMeetingTableByUserId($congressId, $user_sender->user_id);
      $tableFix = $this->meetingServices->getMeetingTableByUserId($congressId, $user_receiver->user_id);
      if ($tableFixSender) {
        $this->meetingServices->addTableToMeeting($meeting, $tableFixSender->meeting_table_id);
      } else if ($tableFix) {
        $this->meetingServices->addTableToMeeting($meeting, $tableFix->meeting_table_id);
      } else if ($nb_meeting_tables > 0) {
        $this->affectTablesToMeeting($meeting, $user_meeting, $congressId, $request);
      }
      $conflicts = $this->meetingServices->getMeetingConflicts($meeting, $user_sender->user_id, $user_receiver->user_id);
      if (sizeof($conflicts) > 0) {
        $this->declineConflictsMeetings($conflicts, $user_meeting, $congress, $user_receiver);
      }
      $this->sendAcceptMeetingsMail($congress, $user_sender, $meeting, $user_receiver);
    } else if (($user_meeting->status == 1) && ($status == -1)) {
      if ($mailtype = $this->congressServices->getMailType('annulation_meeting')) {
        $this->sendAnnulationMail($congress, $mailtype, $user_sender, $meeting, $user_receiver);
      }
    } else {
      $meeting = $this->meetingServices->removeTableFromMeeting($meeting);
      if ($mailtype = $this->congressServices->getMailType('decline_meeting')) {
        $this->sendDeclineMailToUserSender($congress, $mailtype, $user_sender, $meeting, $user_receiver);
      }
    }
    $user_meeting = $this->meetingServices->updateMeetingStatus($user_meeting, $request, $status);
   
    return $meeting;
  }

  public function sendDeclineMailToUserReciever($congress, $mailtype, $user_sender, $meeting, $user_receiver)
  {
    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
      $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
      $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_receiver, $congress, $mail->object, null, $userMail, null, null);
    } else {
      if ($mail = $this->congressServices->getMailOutOfCongress($mailtype->mail_type_id)) {
        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_receiver, $congress, $mail->object, null, $userMail, null, null);
      }
    }
  }

  public function getMeetingsDates($congress_id)
  {
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json(['response' => 'Congress not found', 404]);
    }
    $meetingDates = $this->meetingServices->getmeetingDates($congress_id);
    return response()->json($meetingDates, 200);
  }

  public function setMeetingsDate(Request $request, $congress_id)
  {
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json(["message" => "congress not found"], 404);
    }

    $this->meetingServices->editConfigMeetingDates($request, $congress_id);
    $meetingDates = $this->meetingServices->getmeetingDates($congress_id);

    return response()->json([$meetingDates], 200);
  }

  public function getNumberWaitingMeetings($congressId)
  {
    $user = $this->userServices->retrieveUserFromToken();
    if (!$user) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $congress = $this->congressServices->getCongressById($congressId);
    if (!$congress) {
      return response()->json(['response' => 'No congress found'], 401);
    }
    $NumberOfwaitingMeetings = $this->meetingServices->getNumberOfWaitingMeetings($congress->congress_id, $user->user_id, 0);
    return response()->json($NumberOfwaitingMeetings, 200);
  }

  public function getMeetingsDatesByStartDate($congress_id, $startDate)
  {
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json(['response' => 'Congress not found', 404]);
    }
    $meetingDates = $this->meetingServices->getMeetingsDatesByStartDate($congress_id,$startDate);
    return response()->json($meetingDates, 200);
  }
}
