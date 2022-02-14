<?php

namespace App\Services;


use App\Models\Meeting;
use App\Models\MeetingTable;
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
            ->with(['user_meeting', 'meetingtable'])
            ->first();
    }
    public function getMeetingByUserId($user_id, $congress_id)
    {
        return Meeting::with(['meetingtable', 'user_meeting' => function ($query) {
            $query->with([
                'organizer' => function ($q) {
                    $q->with(['profile_img']);
                }, 'participant' => function ($q) {
                    $q->with(['profile_img']);
                }
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

    public function updateMeetingStatus($user_meeting, $request, $status)
    {
        $user_meeting->status = $status;
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
            $query->where('start_date', '=', $start_date);
        }
        if ($end_date != '' && $end_date != 'null'){
            $query->whereDate('end_date', '<=', date($end_date));
        }
        })
       
        ->count();
    }

    public function getTotalNumberOfMeetingsWithSatuts($congress_id, $status = null)
    {
        return Meeting::whereHas("user_meeting", function ($query) use ($status) {
            if ($status && $status != 'null') {
                $query->where('status', '=', $status);
            }
        })->where('congress_id', '=', $congress_id)
            ->count();
    }

    public function getMeetingsDone($congress_id, $is_participant_present, $is_organizer_present)
    {
        $count = Meeting::where('is_organizer_present', '=', $is_organizer_present)
            ->where('end_date', '<', date('Y-m-d H:i:s'))
            ->where('congress_id', '=', $congress_id)
            ->whereHas("user_meeting", function ($query) use ($is_participant_present) {
                $query->where('is_participant_present', '=', $is_participant_present);
            })
            ->count();
        return  $count;
    }

    public function getRequestDetailsPagination($congress_id, $per_page)
    {
        return Meeting::with(['user_meeting' => function ($query) use ($congress_id) {
            $query->with(['organizer', 'participant' => function ($query) use ($congress_id) {
                $query->with(['user_mails' => function($q) use ($congress_id) {
                    $q->whereHas('meeting', function($q) use ($congress_id) {
                        $q->where('congress_id','=', $congress_id);
                    });
                }]);
            }]);
        }])->where('congress_id', '=', $congress_id)
            ->paginate($per_page);
    }

    public function addMeetingTable($label, $congress_id)
    {
        $meeting_table = new MeetingTable();
        $meeting_table->label = $label;
        $meeting_table->congress_id = $congress_id;
        $meeting_table->save();
        return $meeting_table;
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
        $delete_meetings = MeetingTable::doesnthave('meetings')->where('congress_id', '=', $congress_id)->delete();
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

    public function InsertMeetingTable($nbMeetingTable, $congressId)
    {
        $meetingtables = $this->deleteMeetingTablesWithNoMeeting($congressId);
        for ($i = 1; $i <= $nbMeetingTable; $i++) {
            $label = "Table " . $i;
            $MeetTable = $this->addMeetingTable($label, $congressId);
        }
        if (count($meetingtables) != 0) {
            foreach ($meetingtables as $table) {
                $meetingtables = $this->removeDuplicatesMeetingTable($table->label, $congressId);
            }
        }
    }

    public function countMeetingsByUserOnDate($congress_id, $date, $user_sender_id, $user_reveiver_id)
    {
        return Meeting::whereHas('user_meeting', function ($query) use ($user_sender_id, $user_reveiver_id) {
            $query->where('user_sender_id', '=', $user_sender_id)->where('user_receiver_id','=',$user_reveiver_id);
        })->where('start_date', '=', $date)->where('congress_id', '=', $congress_id)->count();
    }

    public function getMeetingsTimes($startTime, $endTime, $duration, $pause) 
    {
        $meetingsTimes = [];
        if ($startTime == $endTime || $startTime > $endTime)
        {
            $endTime = date("H:i:s", strtotime('11:00 PM'));
        }
        $meetingsTimes[0] = $startTime;
        $i = 0;
        while (isset($meetingsTimes[$i]) && $meetingsTimes[$i] < $endTime) {
            $newTime = $pause == 0 ? date("H:i", strtotime('+' . $duration . ' minutes', strtotime($meetingsTimes[$i]))) : date("H:i", strtotime('+' . $duration + $pause . ' minutes', strtotime($meetingsTimes[$i])));
           if ($newTime < $endTime && date("H:i", strtotime('+' . $duration . ' minutes', strtotime($newTime))) <= $endTime) 
            {
                array_push($meetingsTimes, $newTime);
            }
            $i++;
        }
        return $i;
    }

    public function getTotalNumberOfUserMeetingsBySatuts($congressId, $status)
    {
        return UserMeeting::where('status', '=', $status)
        ->whereHas('meeting', function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        })
        ->count();
    }
}
