<?php

namespace App\Services;


use App\Models\Meeting;
use App\Models\MeetingTable;
use App\Models\UserMeeting;



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
        return Meeting::with(['meetingtable', 'user_meeting' => function ($query) {
            $query->with([
                'organizer' => function ($q) {
                    $q->with(['profile_img']);
                }, 'participant'
            ]);
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

    public function addMeetingTable($label, $congress_id)
    {
        $meetingtable = new MeetingTable();
        $meetingtable->label = $label;
        $meetingtable->congress_id = $congress_id; 
        $meetingtable->save();
        return $meetingtable;
    }

    public function countUsedMeetingTablesByCongressId($congress_id)
    {
        return MeetingTable::whereHas('meetings')->where('congress_id', '=', $congress_id)->count();
    }

    public function getMeetingTablesByCongressId($congress_id)
    {
        return MeetingTable::with(["meetings"])->where('congress_id', '=', $congress_id)->get();
    }

    public function deleteMeetingTablesWithNoMeeting($congress_id)
    {
        $deleteMeetings = MeetingTable::doesnthave('meetings')->where('congress_id', '=', $congress_id)->delete();
        return $this->getMeetingTablesByCongressId($congress_id);
    }

    public function getAvailableMeetingTable($date, $congress_id)
    {
        return MeetingTable::whereDoesntHave('meetings', function ($query) use ($date) {
            $query->where('start_date', '=', $date);
        })->where('congress_id', '=', $congress_id)->first();
    }
 
    public function removeDuplicatesMeetingTable($label, $congress_id)
    {
        return MeetingTable::doesnthave('meetings')->where('congress_id', '=', $congress_id)->where('label', '=', $label)->delete();
    }

    public function addTableToMeeting($meeting, $meetingtable_id){
        $meeting->meeting_table_id = $meetingtable_id;
        $meeting->save();
        return $meeting;
    }

    public function removeTableFromMeeting($meeting){
        $meeting->meeting_table_id = null;
        $meeting->save();
        return $meeting;
    }
}
