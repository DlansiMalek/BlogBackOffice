<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MeetingServices;
use App\Services\UserServices;
use App\Services\AdminServices;
use App\Services\MailServices;
use App\Services\CongressServices;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\UrlUtils;
use App\Services\Utils;
use DateTime;




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
    return $this->meetingServices->getMeetingByUserId($request->input('user_id'), $congress_id);
  }

  function addMeeting(Request $request)
  {
    $congress = $this->congressServices->getCongressDetailsById($request->input('congress_id'));
    $user_sender  = $this->userServices->retrieveUserFromToken();
    $user_receiver = $this->userServices->getUserById($request->input('user_received_id'));
    if (!$request->has('start_date')) {
      return response()->json(['response' => 'Meeting date not found'], 401);
    }
    $meeting_date = $request->input('start_date');
    if (!$user_sender) {
      return response()->json(['response' => 'No user found'], 401);
    }
    if (!$user_receiver) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $duplicated_meeting = $this->meetingServices->countMeetingsByUserOnDate($congress->congress_id, $meeting_date, $user_sender->user_id, $user_receiver->user_id);
    if ($duplicated_meeting > 0) {
      return response()->json(['response' => 'Meeting on the same date found'], 401);
    }
    $user_receiver->meeting_code = Str::random(40);
    $user_receiver->save();
    $meeting = null;
    if ($request->has('meeting_id')) {
      $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
    }
    $userMeet = null;
    if ($request->input('user_meeting')['user_meeting_id']) {
      $userMeet = $this->meetingServices->UserMeetingsById($request->input('user_meeting')['user_meeting_id']);
    }
    $meeting = $this->meetingServices->addMeeting($meeting,  $request);
    $userMeeting = $request->input('user_meeting')['user_meeting_id'] ? $this->meetingServices->editUserMeeting($userMeet) : $this->meetingServices->addUserMeeting($meeting, $userMeet[0], $request, $user_sender->user_id);
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
    if (!$user_receiver = $this->userServices->retrieveUserFromToken()) {
      if ($user_receiver = $this->userServices->getUserById($user_meeting->user_receiver_id)) {
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
    $user_sender = $this->userServices->getUserById($user_meeting->user_sender_id);
    if (!$user_sender) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $user_meeting = $this->meetingServices->updateMeetingStatus($user_meeting, $request, $status);
    if ($status == 1) {
      if ($nb_meeting_tables > 0) {
        $this->affectTablesToMeeting($meeting, $user_meeting, $congressId, $request);
      }
      $conflicts = $this->meetingServices->getMeetingConflicts($meeting, $user_sender->user_id);
      if (sizeof($conflicts) > 0) {
        $this->declineConflictsMeetings($conflicts, $user_meeting, $congress, $user_receiver);
      }
      $this->sendAcceptMeetingsMail($congress, $user_sender, $meeting, $user_receiver);
    } else {
      $meeting = $this->meetingServices->removeTableFromMeeting($meeting);  
      if ($mailtype = $this->congressServices->getMailType('decline_meeting')) {
        $this->sendDeclineMail($congress, $mailtype, $user_sender, $meeting, $user_receiver);
      }
    }
    if ($request->has('verification_code')) {
      $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
      return redirect($linkFrontOffice);
    }
    return response()->json($meeting, 200);
  }

  public function sendDeclineMail($congress, $mailtype, $user_sender, $meeting, $user_receiver)
  {
    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
      $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user_receiver->user_id, null, $meeting->meeting_id);
      $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, $userMail, null, null);
    } else {
      if ($mail = $this->congressServices->getMailOutOfCongress(26)) {
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

  public function getNumberOfMeetingsPerDay($congress_id, Request $request)
  {
    $status = $request->query('status', '1');

    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json('no congress found', 404);
    }
    $datetime1 = new DateTime($congress->start_date);
    $datetime2 = new DateTime($congress->end_date);
    $interval = $datetime2->diff($datetime1);
    $days = $interval->format('%a');
    $nombres = array();

    for ($i = 0; $i <=  $days; $i++) {

      $nombre = $this->meetingServices->getNumberOfMeetings($congress_id, $status, $datetime1->modify('+' . $i . ' day'), null);
      array_push($nombres, (object)[
        'date' => $datetime1->modify('+' . $i . 'day')->format('Y-m-d'),
        'count' => $nombre
      ]);
      //  array_sum($nombres)
    }
    return response()->json($nombres, 200);
  }

  public function getTotalNumberOfMeetings($congress_id)
  {
    $totalNumber = $this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congress_id);
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

    for ($i = -1; $i <=  $days; $i++) {

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
    return $this->meetingServices->getRequestDetailsPagination($congress_id, $per_page);

  }
  public function declineConflictsMeetings($conflicts, $user_meeting, $congress, $user_receiver)
  {
    $mailtype = $this->congressServices->getMailType('decline_meeting');
    foreach ($conflicts as $conflict_meeting) {
      $conflict_meeting = $this->meetingServices->declineMeeting($conflict_meeting['user_meeting']->first());
      $user_sender_conflict = $this->userServices->getUserById($user_meeting->user_sender_id);
      $this->sendDeclineMail($congress, $mailtype, $user_sender_conflict, $conflict_meeting, $user_receiver);
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
}
