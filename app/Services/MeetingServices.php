<?php

namespace App\Services;


use App\Models\Meeting;
use App\Models\MeetingTable;
use App\Models\UserMeeting;
use App\Models\User;
use App\Services\UserServices;


class MeetingServices
{
    protected $userServices;

    function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }
 
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
    public function addUserMeeting($meeting, $user_received_id, $user_id)
    {
        $usermeeting = new UserMeeting();
        $usermeeting->user_sender_id = $user_id;
        $usermeeting->user_receiver_id = $user_received_id;
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

    public function getMeetingConflicts($meet, $user_sender_id, $user_receiver_id)
    {
        return Meeting::where('meeting_id', '!=', $meet->meeting_id)
        ->where('congress_id', '=', $meet->congress_id)
        ->where('start_date', '=', $meet->start_date)
        ->with('user_meeting')
        ->whereHas("user_meeting", function ($query) {
            $query->where('status', '=', 1);
        })
        ->whereHas("user_meeting", function ($query) use ($user_sender_id, $user_receiver_id) {
            $query->where('user_sender_id', '=', $user_sender_id)
            ->orwhere('user_receiver_id', '=', $user_sender_id)
            ->orwhere('user_sender_id', '=', $user_receiver_id)
            ->orwhere('user_receiver_id', '=', $user_receiver_id);
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
        return MeetingTable::with(["meetings"])->where('congress_id', '=', $congress_id)->where('user_id','=',null)->get();
    }

    public function deleteMeetingTablesWithNoMeeting($congress_id)
    {
        $delete_meetings = MeetingTable::doesnthave('meetings')
        ->where('congress_id', '=', $congress_id)
        ->where('user_id','=',null)
        ->delete();
        return $this->getMeetingTablesByCongressId($congress_id);
    }

    public function getAvailableMeetingTable($date, $congress_id)
    {
        return MeetingTable::whereDoesntHave('meetings', function ($query) use ($date) {
            $query->where('start_date', '=', $date);
        })->where('congress_id', '=', $congress_id)
        ->where('user_id', '=', null)->first();
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

    public function getFirstUserMeetingsByMeetingId($meeting_id)
    {
        return UserMeeting::where('meeting_id', '=', $meeting_id)->first();
    }

    public function getFixTables($congress_id)
    {
        return MeetingTable::where('congress_id', '=', $congress_id)
            ->where('user_id', '!=', null)
            ->with(["participant"])
            ->get();
    }

    public function getFixTable($congress_id)
    {
        return MeetingTable::where('congress_id', '=', $congress_id)
            ->where('user_id', '!=', null)
            ->with(["participant"])
            ->first();
    }

    public function getMeetingTableByUserId($congress_id, $user_id)
    {
        return MeetingTable::where('congress_id', '=', $congress_id)
            ->where('user_id', '!=', null)
            ->where('user_id', '=',  $user_id)
            ->with(["participant"])
            ->first();
    }

    public function getMeetingTableById($meeting_table_id)
    {
        return MeetingTable::where('meeting_table_id', '=', $meeting_table_id)
            ->with(['meetings' , 'participant'])->first();
    }

    public function getUserByEmail($email, $congress_id)
    {
        $email = strtolower($email);
        $user = User::whereRaw('lower(email) = (?)', ["{$email}"])
            ->whereHas('user_congresses', function ($query) use ($congress_id) {
                if ($congress_id) {
                    $query->where('congress_id', '=', $congress_id);
                }
            })
            ->first();
        return $user;
    }

    public function setFixTables($newFixTbales, $congress_id)
    {
        $oldFixTables = $this->getFixTables($congress_id);
        $invalidDelete = [];
        $invalidUpdate = [];
        $invalidUser = [];

        foreach ($oldFixTables as  $old) {
            $exists = false;
            $meetingTableDeleted = false;
            foreach ($newFixTbales->all() as $new) {
                if ($old->meeting_table_id == $new['meeting_table_id']) {
                    $exists = true;
                    break;
                }
            }
            $meetingTableById = $this->getMeetingTableById($old->meeting_table_id);
            if (!$exists && count($meetingTableById->meetings) == 0) {
                $old->delete();
                $meetingTableDeleted = true;
                break;
            }
            if (!$meetingTableDeleted && count($meetingTableById->meetings) != 0 && !$exists) {
                array_push($invalidDelete, ' ' .$meetingTableById->participant[0]->email);
            }
        }

        foreach ($newFixTbales->all() as $new) {
            $meetingTable = null;
            $exsistUser = false;
            $user = $this->getUserByEmail($new['participant'][0]['email'], $congress_id);
            if ($user) {
                foreach ($oldFixTables as $old) {
                    if ($old->meeting_table_id == $new['meeting_table_id']) {
                        $meetingTable = $old;
                        $exsistUser = true;
                        break;
                    }
                }
                if (!$meetingTable) {
                    $meetingTable = new MeetingTable();
                    $meetingTable->congress_id = $congress_id;
                }
                if ($exsistUser && $user->user_id != $meetingTable->user_id) {
                    $tableMeeting = $this->getMeetingTableById($meetingTable->meeting_table_id);
                    if (count($tableMeeting->meetings) != 0) {
                        array_push($invalidUpdate, ' ' .$user->email);
                    } else {
                        $meetingTable->user_id = $user->user_id;
                    }
                }
                if (!$exsistUser) {
                    $meetingTable->user_id = $user->user_id;
                }
                $meetingTable->label = $new["label"];
                $meetingTable->banner = $new["banner"];
                $meetingTable->save();
            } else {
                array_push($invalidUser, ' ' .$new['participant'][0]['email']);
            }
        }
        return ['InvalidUpdate' => $invalidUpdate, 'InvalidDelete' => $invalidDelete,  'InvalidUser' => $invalidUser];
    }

    public function InsertFixTable($nbFixTable, $tableFix)
    {
        for ($i = 1; $i <= $nbFixTable; $i++) {
            $label = "Table " . $i;
            $tableFix[$i - 1]->label = $label;
            $tableFix[$i - 1]->update();
        }
    }
    
    public function getMeetingTableByCongress($congress_id, $perPage, $search)
    {
        $listMeetingTables= MeetingTable::where('congress_id', '=', $congress_id)
        ->with(['meetings'])
        ->where(function ($query) use ($search) {
            if ($search != "") {
                $query->whereRaw('lower(label) like (?)', ["%{$search}%"]);
            }
        });
    return $listMeetingTables->paginate($perPage);
    }    
  
    public function getMeetingPlanning($meeting_id)
    {
        $MeetingPlanning= UserMeeting::where('meeting_id', '=', $meeting_id)
        ->with(['meeting', 'organizer','participant'])
        ->first();
    return $MeetingPlanning;
    }    
  
}
