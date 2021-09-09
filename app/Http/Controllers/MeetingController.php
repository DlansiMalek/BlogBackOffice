<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use App\Services\MeetingServices;
use App\Services\UserServices;
use App\Services\AdminServices;
use App\Services\MailServices;
use App\Services\CongressServices;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\UrlUtils;


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

  
    public function getUserMeetingById(Request $request)
    {
        return $this->meetingServices->getMeetingByUserId($request->input('user_id'));
    }

    function addMeeting(Request $request)
    {
        $congress = $this->congressServices->getCongressDetailsById($request->input('congress_id'));
        $user_sender  = $this->userServices->retrieveUserFromToken();
        $user_receiver = $this->userServices->getUserById($request->input('user_received_id'));
        if(!$user_sender)
        {
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
        $userMeet= null;
        if($request->input('user_meeting')['user_meeting_id'])
        {
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
        $user_sender  = $this->userServices->retrieveUserFromToken();
        $user_receiver = $this->userServices->getUserById($request->input('user_received_id'));
        if(!$user_sender)
        {
            $user_sender = $this->userServices->getUserById($request->input('user_sender_id'));
            if($request->has('verification_code')){
                $verification_code = $request->input('verification_code');
                if(!$user_sender->verification_code== $verification_code)
                { return response()->json(['response' => 'No verification code found'], 401);}
            }
        }
        if (!$user_receiver) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $status = $request->input('status');
        $meeting = null;
        if ($request->has('meeting_id')) {
            $meeting = $this->meetingServices->getMeetingById($request->input('meeting_id'));
        }
        $user_meeting = $meeting['user_meeting']->first();
        $user_meeting = $this->meetingServices->updateMeetingStatus($user_meeting, $request);
        if ($status == 1) {
            if ($mailtype = $this->congressServices->getMailType('accept_meeting')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_receiver, $congress, $mail->object, null, null, null, null);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
                } else {
                    if ($mail = $this->congressServices->getMailOutOfCongress(25)) {
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender), $user_receiver, $congress, $mail->object, null, null, null, null);
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress,  $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null,  $meeting, $user_receiver, $user_sender),  $user_sender, $congress, $mail->object, null, null, null, null);
                    }
                }
            }
        } else {
            if ($mailtype = $this->congressServices->getMailType('decline_meeting')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null,[], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $meeting, $user_receiver, $user_sender), $user_receiver, $congress, $mail->object, null, null, null, null);
                } else {
                    if ($mail = $this->congressServices->getMailOutOfCongress(26)) {
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_receiver, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_receiver, $congress, $mail->object, null, null, null, null);
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user_sender, null, null, null, null, null, null, null, null, null, null, null, null, [], null, null, null, $meeting, $user_receiver, $user_sender), $user_sender, $congress, $mail->object, null, null, null, null);
                    }
                }
            }
        }
        if ($request->has('verification_code')) {
            $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice();
            return redirect($linkFrontOffice);
        }
        return response()->json($meeting, 200);
    }
}
