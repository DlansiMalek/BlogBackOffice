<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use App\Services\MeetingServices;
use App\Services\UserServices;
use App\Services\AdminServices;
use App\Services\MailServices;
use App\Services\CongressServices;


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

    public function getMeetingById($congressId, $meeting_id)
    {
        return $this->meetingServices->getMeetingById($meeting_id);
    }
    public function getUserMeetingById(Request $request)
    {
        return $this->meetingServices->getUserMeetingById($request->input('user_id'));
    }

    function addMeeting(Request $request)
    {
        $congress = $this->congressServices->getCongressDetailsById($request->input('congress_id'));
        $meeting_duration = $congress->config->meeting_duration;
        $meeting_pause = $congress->config->pause_duration;
        $user_sender  = $this->userServices->retrieveUserFromToken();
        $user_receiver = $this->userServices->getUserById($request->input('user_received_id'));
        if (!$user_sender) {
            return response()->json(['response' => 'No user found'], 401);
        }
        if (!$user_receiver) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $meeting = null;
        if ($request->has('meeting_id')) {
            $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
        }

        $meeting = $this->meetingServices->addMeeting($meeting,  $request);
        $usermeeting = $this->meetingServices->addUserMeeting($meeting,  $request, $user_sender->user_id);
        if ($mailtype = $this->congressServices->getMailType('request_meeting')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, $meeting->meeting_id), $user_receiver, $congress, $mail->object, null, null, null, null);
            }
        }
        return response()->json($meeting, 200);
    }
    public function deleteMeeting($congress_id, $stand_id)
    {
        if (!$stand = $this->standServices->getStandById($stand_id)) {
            return response()->json('no stand found', 404);
        }
        $stand->delete();
        return response()->json(['response' => 'stand deleted'], 200);
    }

    function modiyStatus(Request $request)
    {
        $congress = $this->congressServices->getCongressDetailsById($request->input('congress_id'));
        $user_sender  = $this->userServices->retrieveUserFromToken();
        $user_receiver = $this->userServices->getUserById($request->input('user_received_id'));
        $status = $request->has('status');
        $meeting = null;
        if ($request->has('meeting_id')) {
            $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
        }

        $user_meeting = $meeting['user_meeting']->first();
        $meeting = $this->meetingServices->updatemeetingstatus($user_meeting, $request);

        if ($status == 1) {

            if ($mailtype = $this->congressServices->getMailType('accept_meeting')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null), $user_receiver, $congress, $mail->object, null, null, null, null);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null), $user_sender, $congress, $mail->object, null, null, null, null);
                }
            }
        } else {
            if ($mailtype = $this->congressServices->getMailType('decline_meeting')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null), $user_sender, $congress, $mail->object, null, null, null, null);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null), $user_receiver, $congress, $mail->object, null, null, null, null);
                }
            }
        }
        return response()->json($meeting, 200);
    }
}
