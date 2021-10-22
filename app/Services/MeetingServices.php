<?php

namespace App\Services;


use App\Models\Meeting;
use App\Models\UserMeeting;
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
        $meeting->congress_id = $request->input('congress_id');
        $meeting->save();
        return $meeting;
    }
    public function addUserMeeting($meeting, $usermeeting, $request, $user_id)
    {
        $usermeeting = new UserMeeting();
        $usermeeting->user_sender_id = $user_id;
        $usermeeting->user_receiver_id = $request->input('user_received_id');
        $usermeeting->meeting_id = $meeting->meeting_id;
        $usermeeting->status = 0;
        $usermeeting->save();
        return $usermeeting;
    }
    public function editUserMeeting($meeting)
    {
        $meeting->status = 0;
        $meeting->update();
        return $meeting;
    }
    public function getMeetingById($meeting_id)
    {
        return Meeting::where('meeting_id', '=', $meeting_id)
            ->with(['user_meeting'])
            ->first();
    }
    public function getMeetingByUserId($user_id, $congress_id)
    {
        return Meeting::with(['user_meeting' => function ($query) {
            $query->with(['organizer', 'participant']);
        }])->whereHas("user_meeting", function ($query) use ($user_id) {
            $query->where('user_sender_id', '=', $user_id)
                ->orwhere('user_receiver_id', '=', $user_id);
        })->where('congress_id', '=', $congress_id)
        ->get();
    }
    public function getUserMeetingsByMeetingId($meeting_id)
    {
        return UserMeeting::where('meeting_id', '=', $meeting_id)->get();
    }
    public function UserMeetingsById($user_meeting_id)
    {
        return UserMeeting::where('user_meeting_id', '=', $user_meeting_id)->first();
    }
    
    public function updateMeetingStatus($user_meeting, $request)
    {
        $user_meeting->status = $request->input('status');
        $user_meeting->user_canceler = $request->input('user_canceler')!= 'null' && $request->input('user_canceler')!= null ? $request->input('user_canceler') : null;  
        $user_meeting->save();
        return $user_meeting;
    }

    public function getUserMeetingsById($meeting_id, $user_id )
    {
        return UserMeeting::where('meeting_id', '=', $meeting_id)
        ->where(function ($query) use ($user_id) {
            if ($user_id)
            $query->where('user_sender_id', '=', $user_id)
            ->orwhere('user_receiver_id', '=', $user_id);
        })->first();
    }

    public function getMeetingConflicts($meet, $user_id)
    {
        return Meeting::where('meeting_id', '!=', $meet->meeting_id)
        ->where('congress_id', '=', $meet->congress_id)
        ->where('start_date', '=', $meet->start_date)
        ->with('user_meeting')
        ->whereHas("user_meeting", function ($query) {
            $query->where('status', '=', 1);
        })
        ->whereHas("user_meeting", function ($query) use ($user_id) {
            $query->where('user_sender_id', '=', $user_id)
            ->orwhere('user_receiver_id', '=', $user_id);
        })
        ->get();
    }

    public function declineMeeting($user_meeting)
    {
        $user_meeting->status = -1;
        $user_meeting->save();
        return $user_meeting;
    }
    public function makeOrganizerPresent ($meeting , $is_organizer_present )
    {
      $meeting->is_organizer_present =$is_organizer_present ;
      $meeting->update();
  
    }
    public function makeParticipantPresent ($user_meeting , $is_participant_present)
    {
      $user_meeting->is_participant_present =$is_participant_present ;
      $user_meeting->update();
    }

    public function getNumberOfMeetings($congress_id, $status = null,$start_date = null,$end_date = null)
    {
        return Meeting::whereHas("user_meeting", function ($query) use ($status) {
            $query->where('status', '=', $status);
        })->where('congress_id', '=', $congress_id)
        ->where(function ($query) use ($start_date, $end_date) {
            if ($start_date != '' && $start_date != 'null'){
            $query->whereDate('start_date', '=', $start_date->format('Y-m-d'));
        }
        if ($end_date != '' && $end_date != 'null'){
            $query->whereDate('end_date', '<=', date($end_date));
        }
        })
       
        ->count();
    }

    public function getTotalNumberOfMeetingsWithSatuts($congress_id, $status)
    {
        return Meeting::whereHas("user_meeting", function ($query) use ($status) {
            if($status!='null')
            {
            $query->where('status', '=', $status);
            }
        })->where('congress_id', '=', $congress_id)       
        ->count();
    }

    public function getTotalNumberOfMeetings($congress_id)
    {
        return Meeting::whereHas("user_meeting")->where('congress_id', '=', $congress_id)       
        ->count(); 
    }
}
