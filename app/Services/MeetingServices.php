<?php

namespace App\Services;


use App\Models\Meeting;
use App\Models\MeetingTable;
use App\Models\UserMeeting;
use App\Models\MeetingEvaluation;



use App\Models\User;
use App\Models\UserCongress;
use App\Models\ConfigCongress;
use App\Models\FormInput;
use App\Models\FormInputResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
class MeetingServices
{
    function __construct( )
    {

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
    
    public function getMeetingByUserId($user_id, $congress_id, $status)
    {
        return Meeting::with(['meeting_evaluation' => function ($query) use ($user_id) {
            $query->where('user_id', '=', $user_id);
        },'meetingtable', 'user_meeting' => function ($query)  use ($congress_id) {
            $query->with([
                'organizer' => function ($q)  use ($congress_id) {
                    $q->with(['profile_img', 'user_congresses' => function ($query) use ($congress_id) {
                        $query->where('congress_id', '=', $congress_id);
                    }]);
                },
                'organizer.responses' => function ($query) use ($congress_id) {
                    $query->whereHas('form_input', function ($query) use ($congress_id) {
                        $query->where('congress_id', '=', $congress_id);
                    });
                },
                'organizer.responses.form_input', 'organizer.responses.form_input.values', 'organizer.responses.form_input.type',
                'organizer.responses.values' => function ($query) {
                    $query->with(['val']);
                },
                'participant' => function ($q) use ($congress_id) {
                    $q->with(['profile_img', 'user_congresses' => function ($query) use ($congress_id) {
                        $query->where('congress_id', '=', $congress_id);
                    }]);
                },
                'participant.responses' => function ($query) use ($congress_id) {
                    $query->whereHas('form_input', function ($query) use ($congress_id) {
                        $query->where('congress_id', '=', $congress_id);
                    });
                }, 'participant.responses.values' => function ($query) {
                    $query->with(['val']);
                },
                'participant.responses.form_input.values', 'participant.responses.form_input.type', 'participant.responses.form_input',
            ]);
        }])->whereHas("user_meeting", function ($query) use ($user_id) {
            $query->where('user_sender_id', '=', $user_id)
                ->orwhere('user_receiver_id', '=', $user_id);
        })->whereHas("user_meeting", function ($query) use ($status) {
            if ($status != '') { 
                $query->where('status', '=' , $status);
            }
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

    public function getNumberOfMeetings($congress_id, $status = null,$start_date = null)
    {
        return Meeting::whereHas("user_meeting", function ($query) use ($status) {
                $query->where('status', '=', $status);
        })->where('congress_id', '=', $congress_id)
        ->where(function ($query) use ($start_date) {
            if ($start_date != '' && $start_date != 'null'){
            $query->whereDate('start_date', $start_date);
        }
        })
        ->count();
    }

    public function getTotalNumberOfMeetingsWithSatuts($congress_id, $status, $startDate = null, $endDate = null)
    {
        return Meeting::whereHas("user_meeting", function ($query) use ($status) {
                $query->where('status', '=', $status);
        })->where('congress_id', '=', $congress_id)
        ->where(function ($query) use ($startDate, $endDate) {
            if ($startDate && $startDate != 'null') {
                $query->whereDate('start_date', '>=', $startDate);
            }
            if ($endDate && $endDate != 'null') {
                $query->whereDate('end_date', '<=', $endDate);
            }
        })->count();
    }

    public function getTotalNumberOfMeetingsByCongress($congress_id)
    {
        return Meeting::whereHas("user_meeting")
        ->where('congress_id', '=', $congress_id)
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

    public function getRequestDetailsPagination($congress_id, $per_page, $startDate, $endDate, $search)
    {
        return Meeting::with(['user_meeting' => function ($query) use ($congress_id) {
            $query->with(['organizer', 'participant' => function ($query) use ($congress_id) {
                $query->with(['user_mails' => function($q) use ($congress_id) {
                    $q->whereHas('meeting', function($q) use ($congress_id) {
                        $q->where('congress_id','=', $congress_id);
                    });
                }]);
            }]);
        }])->where(function ($query) use ($startDate, $endDate, $search) {
            if ($search !== '' && $search !== null && $search !== 'null') {
                $query->whereRaw('lower(name) like (?)', ["%{$search}%"]);
            }
            if ($startDate != '' && $startDate != null && $startDate != 'null') {
                $query->whereDate('start_date', '>=', $startDate)
                ->whereDate('end_date', '<=', $endDate);
            }
        })
        ->where('congress_id', '=', $congress_id)
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
        return MeetingTable::whereHas('meetings')
        ->where('congress_id', '=', $congress_id)
        ->where('user_id', '=', null)
        ->count();
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

    public function InsertMeetingTable($nbMeetingTable, $congressId, $congress)
    {
        $meetingtables = $this->deleteMeetingTablesWithNoMeeting($congressId);
        $labelVarTables = $congress->config->label_meeting_table != null ? $congress->config->label_meeting_table : 'TV';
        $nbFixTable = $congress->config->nb_fix_table;

        for ($i = $nbFixTable + 1 ; $i <= $nbMeetingTable + $nbFixTable ; $i++) {
            $label = $labelVarTables . ' ' . $i;
            $MeetTable = $this->addMeetingTable($label, $congressId);
        }
        if (count($meetingtables) != 0) {
            foreach ($meetingtables as $table) {
                $meetingtables = $this->removeDuplicatesMeetingTable($table->label, $congressId);
            }
        }
    }

    public function countMeetingsByUserOnDate($congress_id, $date, $user_sender_id, $user_reveiver_id, $status)
    {
        return Meeting::whereHas('user_meeting', function ($query) use ($user_sender_id, $user_reveiver_id, $status) {
            $query->where('user_sender_id', '=', $user_sender_id)
            ->where('user_receiver_id','=',$user_reveiver_id)
            ->where('status', '=',  $status);
        })->where('start_date', '=', $date)->where('congress_id', '=', $congress_id)->count();
    }

    public function addMeetingEvaluation($request , $user_id)
    {
        $meetingEvaluation = new MeetingEvaluation();
        $meetingEvaluation->note = $request->input('note');
        $meetingEvaluation->comment = $request->input('comment');
        $meetingEvaluation->user_id = $user_id;
        $meetingEvaluation->meeting_id =  $request->input('meeting_id');
        $meetingEvaluation->save();
        return $meetingEvaluation;
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

    public function getUserByEmail($email, $congress_id , $isSelected)
    {
        $email = strtolower($email);
        $user = User::whereRaw('lower(email) = (?)', ["{$email}"])
            ->whereHas('user_congresses', function ($query) use ($congress_id , $isSelected) {
                if ($congress_id) {
                    $query->where('congress_id', '=', $congress_id);
                }
                $query->whereHas('congress', function ($query) use ($isSelected) {
                   if($isSelected != null){
                    $query->where('isSelected','=' ,1);
                }
                });
            })
            ->first();
        return $user;
    }

    public function setFixTables($newFixTbales, $congress_id, $isSelected = null)
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
            $user = $this->getUserByEmail($new['participant'][0]['email'], $congress_id , $isSelected);
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

                $userCongress = $this->getUserCongressByUserId($congress_id, $user->user_id);
                if ($userCongress) {
                    $fix_table_info = $this->getFixTableInfo($congress_id);
                    $fix_table_info = $fix_table_info[0]["show_in_fix_table"];
                    $form_input = $this->getQuestionByKey($congress_id, $fix_table_info);
                    if ($form_input) {
                        if ($form_input->form_input_type_id == 6 ||  $form_input->form_input_type_id == 7 || $form_input->form_input_type_id == 8 || $form_input->form_input_type_id == 9) {
                            $fix_table_info = $this->getValueResponse($user->user_id, $form_input->form_input_id);
                            $userCongress->fix_table_info = $fix_table_info[0]['values'][0]['val']['value'];
                        } else {
                            $fix_table_info = $this->getResponseFormInput($user->user_id, $form_input->form_input_id);
                            $userCongress->fix_table_info = $fix_table_info[0]['response'];
                        }
                        $userCongress->update();
                    }
                }
            } else {
                array_push($invalidUser, ' ' .$new['participant'][0]['email']);
            }
        }
        return ['InvalidUpdate' => $invalidUpdate, 'InvalidDelete' => $invalidDelete,  'InvalidUser' => $invalidUser];
    }

    public function InsertFixTable($nbFixTable, $tableFix, $labelFixTables)
    {
        for ($i = 1; $i <= $nbFixTable; $i++) {
            $label = $labelFixTables . ' ' . $i;
            $tableFix[$i - 1]->label = $label;
            $tableFix[$i - 1]->update();
        }
    }

    public function getMeetingTableByCongress($congress_id, $perPage, $search)
    {
        $listMeetingTables = MeetingTable::where('congress_id', '=', $congress_id)
            ->with(['meetings', 'participant.user_congresses'])
            ->where(function ($query) use ($search) {
                if ($search != "") {
                    $query->whereRaw('lower(label) like (?)', ["%{$search}%"])
                    ->orWhereHas('participant.user_congresses', function ($query) use ($search) {
                        $query->whereRaw('lower(fix_table_info) like (?)', ["%{$search}%"]);
                    });
                }
            });

        return $listMeetingTables->paginate($perPage);
    }    
  
    public function getMeetingPlanning($meeting_table_id)
    {
        $MeetingPlanning= MeetingTable::where('meeting_table_id', '=', $meeting_table_id)
        ->with(['meetings.user_meeting.organizer', 'meetings.user_meeting.participant','meetings'])
        ->first();
    return $MeetingPlanning;
    }    

    public function getCachedFixTables($congress_id, $page, $perPage, $search)
    {

        $cacheKey = config('cachedKeys.FixTables') . $congress_id . $page . $perPage . $search;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $fixTables = $this->getFixTablesWithPagination($congress_id, $perPage, $search);
        Cache::put($cacheKey, $fixTables, env('CACHE_EXPIRATION_TIMOUT', 300)); // 5 minutes;
        return $fixTables;

    }

    public function getFixTablesWithPagination($congress_id, $perPage = null, $search)
    {
        $allFixTables = MeetingTable::where('congress_id', '=', $congress_id)
            ->where('user_id', '!=', null)
            ->with(["participant.user_congresses" => function ($query) use ($congress_id){
                $query->where('congress_id', '=', $congress_id);
            }])
            ->where(function ($query) use ($search) {
                if ($search !== '' && $search != null && $search != 'null') {
                    $query->whereRaw('lower(label) like (?)', ["%{$search}%"])
                    ->orWhereHas('participant', function ($query) use ($search) {
                        $query->whereRaw('lower(first_name) like (?)', ["%{$search}%"])
                        ->orWhereRaw('lower(last_name) like (?)', ["%{$search}%"]);
                    })->orWhereHas('participant.user_congresses', function ($query) use ($search) {
                            $query->whereRaw('lower(fix_table_info) like (?)', ["%{$search}%"]);
                        });
                }
            });
        return  $allFixTables = $perPage ? $allFixTables->paginate($perPage) : $allFixTables->get();
    }

    public function getFixTableInfo($congress_id)
    {
        return ConfigCongress::where('congress_id', '=', $congress_id)
            ->get('show_in_fix_table');
    }

    public function getUserCongressByUserId($congressId, $userId)
    {
        return UserCongress::where('congress_id', '=', $congressId)
        ->where('user_id', '=', $userId)->first();
    }

    public function getQuestionByKey($congress_id,$key)
    {
        return FormInput::where('congress_id', '=', $congress_id)
         ->where('key','=',$key)
         ->first();
    }

    public function getValueResponse($user_id, $form_input_id)
    {
        return FormInputResponse::where('user_id', '=', $user_id)
        ->where('form_input_id', '=', $form_input_id) 
        ->with(['values'  => function ($query) {
            $query->with(['val']);
        }]) 
        ->get();
    }

    public function getResponseFormInput($user_id, $form_input_id)
    {
        return FormInputResponse::where('user_id', '=', $user_id)
            ->where('form_input_id', '=', $form_input_id)   
            ->get('response');
    }

    public function renameTables($tables, $newLabel)
    {
        foreach( $tables as $table) {
            $int = (int) filter_var($table->label, FILTER_SANITIZE_NUMBER_INT);
            $table->label = $newLabel . ' ' . $int;
            $table->update();
        }
    }

    public function getVariableTables($congressId)
    {
        return MeetingTable::where('congress_id', '=', $congressId)
        ->where('user_id', '=', null)
        ->get();
    }

    public function resetTablesCounter($tables, $newLabel, $counter)
    {
        foreach( $tables as $table) {
            $counter +=1;
            $table->label = $newLabel . ' ' . $counter;
            $table->update();
        }
    }
  
}
