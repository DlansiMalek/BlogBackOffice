<?php

namespace App\Services;


use App\Models\Meeting;
use App\Models\UserMeeting;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Illuminate\Support\Facades\Log;



class MeetingServices
{
    public function addMeeting($meeting, $request)
    {
        if (!$meeting) {
            $meeting = new Meeting();
        }
        $meeting->name       = $request->input('name');
        $meeting->start_date = $request->input('start_date');
        $meeting->end_date   = $request->input('end_date');
        $meeting->save();
        return $meeting;
    }
    public function addUserMeeting($meeting, $usermeeting, $request, $user_id)
    {
        if (!$usermeeting) {
            $usermeeting = new UserMeeting();
        }
        $usermeeting->user_sender_id     = $user_id;
        $usermeeting->user_receiver_id   = $request->input('user_received_id');
        $usermeeting->meeting_id         = $meeting->meeting_id;
        $usermeeting->status   = 0;
        $usermeeting->save();
        return $usermeeting;
    }
    public function getMeetingById($meeting_id)
    {
        return Meeting::where('meeting_id', '=', $meeting_id)
            ->with(['user_meeting'])
            ->first();
    }
    public function getMeetingByUserId($user_id)
    {
        return Meeting::with(['user_meeting'])->whereHas("user_meeting", function ($query) use ($user_id) {
            $query->where('user_sender_id', '=', $user_id)
                ->orwhere('user_receiver_id', '=', $user_id);
        })->get();
    }
    public function getUserMeetingsByMeetingId($meeting_id)
    {
        return UserMeeting::where('meeting_id', '=', $meeting_id)->get();
    }
    public function UserMeetingsById($user_meeting_id)
    {
        return UserMeeting::where('user_meeting_id', '=', $user_meeting_id)->get();
    }
    
    public function updateMeetingStatus($user_meeting, $request)
    {
       
        $user_meeting->status = $request->input('status');
        $user_meeting->user_canceler = $request->input('user_canceler')!= null?$request->input('user_canceler') : null ;   
        $user_meeting->save();
        return $user_meeting;
    }

    public function getUserMeetingsById($meeting_id, $user_id = null)
    {
        return UserMeeting::where('meeting_id', '=', $meeting_id)
        ->where(function ($query) use ($user_id) {
            if ($user_id)
            $query->where('user_sender_id', '=', $user_id)
            ->orwhere('user_receiver_id', '=', $user_id);
        })->first();
    }
}
