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
    if (!$user_sender) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $user_sender->verification_code = Str::random(40);
    $user_sender->save();
    if (!$user_receiver) {
      return response()->json(['response' => 'No user found'], 401);
    }
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
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender, $user_sender->verification_code), $user_receiver, $congress, $mail->object, null, null, null, null);
      } else {
        if ($mail = $this->congressServices->getMailOutOfCongress(24)) {
          $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender, $user_sender->verification_code), $user_receiver, $congress, $mail->object, null, null, null, null);
        }
      }
    }
    if ($request->has('verification_code')) {
      $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
      return redirect($linkFrontOffice);
    }
    return response()->json($meeting, 200);
  }

  function modiyStatus(Request $request)
  {
    $congress = $this->congressServices->getCongressDetailsById($request->input('congress_id'));
    $user_receiver = $this->userServices->getUserById($request->input('user_received_id'));

    if (!$user_receiver) {
      return response()->json(['response' => 'No user found'], 401);
    }
    $status = $request->input('status');
    $meeting = null;
    if ($request->has('meeting_id')) {
      $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
    }
    $user_meeting = $meeting['user_meeting']->first();
    $user_sender = $this->userServices->getUserById($user_meeting->user_sender_id);
    if (!$user_sender) {
      return response()->json(['response' => 'No user found'], 401);
    }
    if ($request->has('verification_code')) {
      $verification_code = $request->input('verification_code');
      if (!$user_sender->verification_code == $verification_code) {
        return response()->json(['response' => 'No verification code found'], 401);
      }
    }
    $user_meeting = $this->meetingServices->updateMeetingStatus($user_meeting, $request);
    if ($status == 1) {
      $conflicts = $this->meetingServices->getMeetingConflicts($meeting, $user_sender->user_id);
      if (sizeof($conflicts) > 0) {
        foreach ($conflicts as $conflict_meeting) {
          $conflict_meeting = $this->meetingServices->declineMeeting($conflict_meeting['user_meeting']->first());
          $user_sender_conflict = $this->userServices->getUserById($user_meeting->user_sender_id);
          if ($mailtype = $this->congressServices->getMailType('decline_meeting')) {
            $this->sendDeclineMail($congress, $mailtype, $user_sender_conflict, $conflict_meeting, $user_receiver);
          }
        }
      }
      if ($mailtype = $this->congressServices->getMailType('accept_meeting')) {
        if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
          $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
        } else {
          if ($mail = $this->congressServices->getMailOutOfCongress(25)) {
            $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress,  $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender),  $user_sender, $congress, $mail->object, null, null, null, null);
          }
        }
      }
    } else {
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
      $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
    } else {
      if ($mail = $this->congressServices->getMailOutOfCongress(26)) {
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
      }
    }
  }

  public function makeOrganizerPresent ($meeting_id , Request $request )
  {
    $meeting= $this->meetingServices->getMeetingById($meeting_id);
    $this->meetingServices->makeOrganizerPresent($meeting, $request->input('is_organizer_present') );
  }

  public function makeParticipantPresent ($meeting_id , Request $request)
  {
    $user_meeting=$this->meetingServices->getUserMeetingsById($meeting_id,$request->input('user_id'));
    $this->meetingServices->makeParticipantPresent($user_meeting, $request->input('is_participant_present'));
  }

  public function getNumberOfMeetingsPerDay($congress_id, Request $request)
  {
    $status = $request->query('status','1');
   
    if (!$congress = $this->congressServices->getCongressById($congress_id)) {
      return response()->json('no congress found', 404);
  }
  $datetime1 = new DateTime($congress->start_date);
$datetime2 = new DateTime($congress->end_date);
$interval = $datetime2->diff($datetime1);
$days = $interval->format('%a');
$nombres = array();

for ($i = 0; $i <=  $days ; $i++)
{

  $nombre=$this->meetingServices->getNumberOfMeetings($congress_id,$status,$datetime1->modify('+'.$i.' day'),null);
  array_push($nombres,(object)[
    'date' => $datetime1->modify('+'.$i.'day')->format('Y-m-d'),
    'count' => $nombre
]);
  //  array_sum($nombres)
}
return response()->json($nombres, 200);


  }

  public function getTotalNumberOfMeetings($congress_id)
{
  $nombre_total=$this->meetingServices->getTotalNumberOfMeetings($congress_id);
  $nombre_meetings_accpeted=$this->meetingServices->getTotalNumberOfMeetingsWithSatuts($congress_id,1);
  $nombre_meetings_done= $this->meetingServices->getMeetingsDone($congress_id,1,1);
  $pourcentage_meetings_accpeted=($nombre_meetings_accpeted/$nombre_total);
  $pourcentage_meetings_done=($nombre_meetings_done/$nombre_meetings_accpeted);
  return response()->json(["pourcentage_meetings_accpeted"=>$pourcentage_meetings_accpeted
                          ,"pourcentage_meetings_done"=>$pourcentage_meetings_done], 200);

}

public function getNumberOfMeetings($congress_id, Request $request)
{
  if (!$congress = $this->congressServices->getCongressById($congress_id)) {
    return response()->json('no congress found', 404);
}
$datetime1 = new DateTime($congress->start_date);
$datetime2 = new DateTime($congress->end_date);
$interval = $datetime2->diff($datetime1);
$days = $interval->format('%a');
$nombres = array();

for ($i = -1; $i <=  $days ; $i++)
{

  $nombre_meetings_accpeted=$this->meetingServices->getNumberOfMeetings($congress_id,1,date('Y-m-d', strtotime($congress->start_date. ' +'. $i .'days')));
  $nombre_meetings_Refused=$this->meetingServices->getNumberOfMeetings($congress_id,-1,date('Y-m-d', strtotime($congress->start_date. ' +'. $i .'days')));
  $nombre_meetings_waiting=$this->meetingServices->getNumberOfMeetings($congress_id,0,date('Y-m-d', strtotime($congress->start_date. ' +'. $i .'days')));
  array_push($nombres, date('Y-m-d', strtotime($congress->start_date. ' +'. $i .'days')),[[
    
    "label"=>"MeetingsAccpeted",
    "value"=>$nombre_meetings_accpeted ,
  ],
  [
    "label"=>"MeetingsRefused",
    "value"=>$nombre_meetings_Refused ,
  ],
  [
    "label"=>"MeetingsWaiting ",
   "value"=>$nombre_meetings_waiting 
  ] 
]);

}
return response()->json($nombres, 200);



}

public function getRequestDetailsPagination($congress_id, Request $request){
  $per_page = $request->query('perPage', 10);
  return $this->meetingServices->getRequestDetailsPagination($congress_id,$per_page);
}
}
