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
    $userMeet = null;
    if ($request->has('meeting_id')) {
      $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
      $userMeet = $this->meetingServices->getFirstUserMeetingsByMeetingId($meeting->meeting_id);
    }
    $meeting = $this->meetingServices->addMeeting($meeting,  $request);
    $userMeeting = $request->has('meeting_id') ? $this->meetingServices->editUserMeeting($userMeet) : $this->meetingServices->addUserMeeting($meeting, $userMeet[0], $request, $user_sender->user_id);
    if ($mailtype = $this->congressServices->getMailType('request_meeting')) {
      if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender, $user_receiver->meeting_code), $user_receiver, $congress, $mail->object, null, null, null, null);
      } else {
        if ($mail = $this->congressServices->getMailOutOfCongress(24)) {
          $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender, $user_receiver->meeting_code), $user_receiver, $congress, $mail->object, null, null, null, null);
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

    if ($status == 1) {
      $tableFix = $this->meetingServices->getFixTable($congressId);
      $nbTableFix = $tableFix->count();
      if($nbTableFix>0){
        if( $tableFix->user_id == $user_receiver->user_id){
          $this->meetingServices->addTableToMeeting($meeting, $tableFix->meeting_table_id);
        }
      }
      if ($nb_meeting_tables > 0) {
        $this->affectTablesToMeeting($meeting, $user_meeting, $congressId, $request);
      }
      $conflicts = $this->meetingServices->getMeetingConflicts($meeting, $user_sender->user_id);
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
        $this->sendDeclineMail($congress, $mailtype, $user_sender, $meeting, $user_receiver);
      }
    }
    $user_meeting = $this->meetingServices->updateMeetingStatus($user_meeting, $request, $status);
    if ($request->has('verification_code')) {
      $linkFrontOffice = UrlUtils::getUserMeetingLinkFrontoffice($congressId);
      return redirect($linkFrontOffice);
    }
    return response()->json($meeting, 200);
  }

  public function sendDeclineMail($congress, $mailtype, $user_sender, $meeting, $user_receiver)
  {
    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
      $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
    } else {
      if ($mail = $this->congressServices->getMailOutOfCongress(26)) {
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
      }
    }
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
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender, null, $meetingtable['label']), $user_sender, $congress, $mail->object, null, null, null, null);
      } else {
        if ($mail = $this->congressServices->getMailOutOfCongress(25)) {
          $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress,  $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender, null, $meetingtable['label']),  $user_sender, $congress, $mail->object, null, null, null, null);
        }
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

  public function setFixTables(Request $request, $congress_id)
  {
    $errorTables = $this->meetingServices->setFixTables($request, $congress_id);
    $fixTables = $this->meetingServices->getFixTables($congress_id);
    $nbTableFix = $fixTables->count();

    if ($nbTableFix != 0) {
      $this->meetingServices->InsertFixTable($nbTableFix, $fixTables);
    }
    return response()->json(['fixTables' => $fixTables, 'erorTables' => $errorTables], 200);
  }

  public function getFixTables($congress_id)
  {
    return $this->meetingServices->getFixTables($congress_id);
  }
}
