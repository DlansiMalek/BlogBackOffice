<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\Models\Access;
use App\Models\AccessChair;
use App\Models\AccessGame;
use App\Models\AccessSpeaker;
use App\Models\AccessType;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\AccessPresence;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AccessServices
{

    protected $resourcesServices;

    function __construct(ResourcesServices $resourcesServices)
    {
        $this->resourcesServices = $resourcesServices;
    }


    public function addAccess($congress_id, Request $request)
    {
        $access = new Access();
        $access->name = $request->input("name");
        $access->start_date = $request->input("start_date");
        $access->end_date = $request->input("end_date");
        $access->access_type_id = $request->input('access_type_id');
        $access->display_time = $request->input('display_time');
        if ($request->has('price')) $access->price = $request->input("price");
        if ($request->has('packless')) $access->packless = $request->input("packless") ? 1 : 0;
        if ($request->has('description')) $access->description = $request->input("description");
        if ($request->has('room')) $access->room = $request->input("room");
        if ($request->has('topic_id')) $access->topic_id = $request->input("topic_id");
        if ($request->has('seuil')) $access->seuil = $request->input('seuil');
        if ($request->has('max_places')) $access->max_places = $request->input('max_places');
        if ($request->has('lp_speaker_id')) $access->lp_speaker_id = $request->input('lp_speaker_id');
        $access->show_in_program = (!$request->has('show_in_program') || $request->input('show_in_program')) ? 1 : 0;
        if ($request->has('banner')) $access->banner = $request->input("banner");

        if ($request->has('show_in_register'))
            $access->show_in_register = $request->input('show_in_register');

        if ($request->has('with_attestation'))
            $access->with_attestation = $request->input('with_attestation');

        if ($request->has('is_online'))
            $access->is_online = $request->input('is_online');

        $access->congress_id = $congress_id;
        $access->save();
        return $access;
    }

    public function editAccess($access, Request $request)
    {
        if ($request->has('name')) $access->name = $request->input("name");
        if ($request->has('start_date')) $access->start_date = $request->input("start_date");
        if ($request->has('end_date')) $access->end_date = $request->input("end_date");
        if ($request->has('access_type_id')) $access->access_type_id = $request->input('access_type_id');
        if ($request->has('price')) $access->price = $request->input("price");
        if ($request->has('packless')) $access->packless = $request->input("packless") ? 1 : 0;
        if ($request->has('description')) $access->description = $request->input("description");
        if ($request->has('room')) $access->room = $request->input("room");
        if ($request->has('topic_id')) $access->topic_id = $request->input("topic_id");
        if ($request->has('seuil')) $access->seuil = $request->input('seuil');
        if ($request->has('max_places')) $access->max_places = $request->input('max_places');
        if ($request->has('show_in_program')) $access->show_in_program = (!$request->has('show_in_program') || $request->input('show_in_program')) ? 1 : 0;
        if ($request->has('url_streaming')) $access->url_streaming = $request->input("url_streaming");
        if ($request->has('lp_speaker_id')) $access->lp_speaker_id = $request->input('lp_speaker_id');
        if ($request->has('banner')) $access->banner = $request->input('banner');
        $access->display_time = $request->input('display_time');
        if ($request->has('show_in_register'))
            $access->show_in_register = $request->input('show_in_register');

        if ($request->has('with_attestation'))
            $access->with_attestation = $request->input('with_attestation');

        if ($request->has('is_online'))
            $access->is_online = $request->input('is_online');
            if ($request->has('max_online_participants')) $access->max_online_participants = $request->input('max_online_participants');
        
        $access->update();
        return $access;
    }

    public function getById($accessId)
    {
        return Access::find($accessId);
    }

    public
    function getIntuitiveAccess($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->where('intuitive', '=', 1)
            ->get();
    }

    public
    function getIntuitiveAccessIds($congressId)
    {
        $accesss = $this->getIntuitiveAccess($congressId);
        Log::info($accesss);
        $res = array();
        foreach ($accesss as $access) {
            $accessId = $access->access_id;
            array_push($res, $accessId);
        }
        return $res;
    }

    public
    function getAccessIdsByAccess($accesss)
    {
        $res = array();
        foreach ($accesss as $access) {
            array_push($res, $access->access_id);
        }
        return $res;
    }

    public
    function getUserAccessByAccessId($accessId)
    {
        return User::whereHas('accesss', function ($query) use ($accessId) {
            $query->where('Access.access_id', '=', $accessId);
        })
            ->get();
    }

    public
    function getAllAccessByAccessIds($accessIds)
    {
        return $accessIds ? Access::whereIn('access_id', $accessIds)->get() : [];
    }

    public function addChairs(Access $access, $chairs)
    {
        foreach ($chairs as $chair) {
            $this->addChair($access['access_id'], $chair);
        }
    }

    public function addChair($access_id, $user_id)
    {
        $access_chair = new AccessChair();
        $access_chair->access_id = $access_id;
        $access_chair->user_id = $user_id;
        $access_chair->save();
    }

    public function addSpeakers(Access $access, $speakers)
    {
        foreach ($speakers as $speaker) {
            $this->addSpeaker($access['access_id'], $speaker);
        }
    }

    public function addSpeaker($access_id, $user_id)
    {
        $access_speaker = new AccessSpeaker();
        $access_speaker->access_id = $access_id;
        $access_speaker->user_id = $user_id;
        $access_speaker->save();
    }

    public function addSubAccesses(Access $access, $sub_accesses)
    {
        foreach ($sub_accesses as $sub) {
            $this->addSubAccess($access, $sub);
        }
    }

    public function getAccessById($access_id)
    {
        return Access::with(
            [
                'votes.access',
                'speakers', 'chairs', 'topic', 'resources', 'type',
                'sub_accesses.speakers', 'sub_accesses.chairs', 'sub_accesses.topic', 'sub_accesses.resources', 'sub_accesses.type', 'speaker'])
            ->find($access_id);
    }

    public function getCachedByCongressId ($congress_id) {
        $cacheKey = 'accesses-congress-' . $congress_id;

        if (Cache::has($cacheKey)) {
            $congress = Cache::get($cacheKey);
        } else {
            $congress = $this->getByCongressId($congress_id);
            Utils::putCacheData($cacheKey, $congress);
        }

        return $congress;
    }

    public function getByCongressId($congress_id)
    {
        return Access::with(['speakers', 'chairs', 'topic', 'resources', 'type',
            'sub_accesses.speakers', 'sub_accesses.chairs', 'sub_accesses.topic', 'sub_accesses.resources', 'sub_accesses.type', 'speaker','privileges'])
            ->whereNull('parent_id')
            ->where('congress_id', '=', $congress_id)
            ->orderBy('start_date')
            ->get();
    }

    public function getAccesssByCongressId($congress_id)
    {
        return Access::where('congress_id', '=', $congress_id)->select('access_id', 'name', 'status')->get();
    }

    public function deleteAccess($access_id)
    {
        Access::where('access_id', '=', $access_id)->delete();
    }

    public function removeAllChairs($access_id)
    {
        AccessChair::where('access_id', "=", $access_id)->delete();
    }

    public function removeAllSpeakers($access_id)
    {
        AccessSpeaker::where('access_id', "=", $access_id)->delete();
    }

    public function editChairs($access_id, $newChairs)
    {
        $oldChairs = AccessChair::where('access_id', "=", $access_id)->get();
        foreach ($oldChairs as $old) {
            $found = false;
            foreach ($newChairs as $new) {
                if ($new == $old->user_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) $old->delete();
        }
        foreach ($newChairs as $new) {
            $found = false;
            foreach ($oldChairs as $old) {
                if ($old->user_id == $new) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $access_chair = new AccessChair();
                $access_chair->access_id = $access_id;
                $access_chair->user_id = $new;
                $access_chair->save();
            }
        }
    }

    public function editSpeakers($access_id, $newSpeakers)
    {
        $oldSpeakers = AccessSpeaker::where('access_id', "=", $access_id)->get();
        foreach ($oldSpeakers as $old) {
            $found = false;
            foreach ($newSpeakers as $new) {
                if ($new == $old->user_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) $old->delete();
        }
        foreach ($newSpeakers as $new) {
            $found = false;
            foreach ($oldSpeakers as $old) {
                if ($old->user_id == $new) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $speaker_access = new AccessSpeaker();
                $speaker_access->access_id = $access_id;
                $speaker_access->user_id = $new;
                $speaker_access->save();
            }
        }
    }

    public function deleteAllSubAccesses($access_id)
    {
        Access::where('parent_id', '=', $access_id)->delete();
    }

    public function editSubAccesses($access, $newSubAccesses)
    {
        $oldSubAccesses = Access::where('parent_id', '=', $access->access_id)->get();
        foreach ($oldSubAccesses as $old) {
            $found = false;
            foreach ($newSubAccesses as $new) {
                if (array_key_exists('access_id', $new) && $new['access_id'] == $old->access_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) $old->delete();
        }
        foreach ($newSubAccesses as $new) {
            $found = false;
            foreach ($oldSubAccesses as $old) {
                if (array_key_exists('access_id', $new) && $new['access_id'] == $old->access_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found)
                $this->addSubAccess($access, $new);
            else $this->editSubAccess($old, $new);

        }

    }

    public function getAccessTypes()
    {
        return AccessType::all();
    }

    public function ChangeAccessPacklessZeroToOne($accessIds, $accesss)
    {

        foreach ($accessIds as $accessId) {
            foreach ($accesss as $access) {

                if ($accessId == $access->access_id) {
                    $access->packless = 1;
                    $access->update();
                }

            }
        }
    }

    public function getAccessTopics()
    {
        return Topic::all();
    }

    public function getMainByCongressId($congress_id)
    {
        return Access::where('congress_id', '=', $congress_id)
            ->whereNull('parent_id')
            ->get();
    }

    public function getAllAccessByRegisterParams($congress_id, $showInRegister, $packless = null)
    {
        return Access::where('show_in_register', '=', $showInRegister)
            ->when($packless === 0 || $packless === 1, function ($query) use ($packless) {
                $query->where('packless', '=', $packless);
            })
            ->whereNull('parent_id')
            ->where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getAllAccessByPackIds($user_id, $congressId, $packIds, $packless, $show_in_register)
    {

        return Access::join('Access_Pack', 'Access_Pack.access_id', '=', 'Access.access_id')
            ->join('User_Pack', 'User_Pack.pack_id', '=', 'Access_Pack.pack_id')
            ->whereIn('User_Pack.pack_id', $packIds)
            ->where('User_Pack.user_id', '=', $user_id)
            ->where('congress_id', '=', $congressId)
            ->where('packless', '=', $packless)
            ->where('show_in_register', '=', $show_in_register)
            ->get();
    }

    public function getChairAccessByAccessAndUser($accessId, $userId)
    {
        return AccessChair::where('user_id', '=', $userId)
            ->where('access_id', '=', $accessId)
            ->first();
    }

    public function getUserAccessByUserId($userId, $congressId)
    {
        ;
        return UserAccess::where('user_id', '=', $userId)
            ->join('Access', 'Access.access_id', '=', 'User_Access.access_id')
            ->where('Access.congress_id', '=', $congressId)
            ->orderBy('Access.start_date', 'asc')
            ->get();

    }

    public function getClosestAccess($userId, $congressId)
    {

        $date = new DateTime(date('Y-m-d H:i:s'));
        $maxDate = mktime(0, 0, 0, 0, 0, 3000); // creation d'une date supperieur à la date actuelle ;
        $diff = $date->diff(new DateTime(date('Y-m-d H:i:s', $maxDate)));
        $closestAccess = new Access();
        $accesss = $this->getUserAccessByUserId($userId, $congressId);
        foreach ($accesss as $access) {
            $accessDate = new DateTime($access->start_date);
            if (
                $diff->days > ($date->diff($accessDate))->days ||
                $diff->h > ($date->diff($accessDate))->h ||
                $diff->s > ($date->diff($accessDate))->s

            ) {
                $diff = $date->diff($accessDate);
                $closestAccess = $access;
            }


        }
        return $closestAccess;
    }

    public function getSpeakerAccessByAccessAndUser($accessId, $userId)
    {
        return AccessSpeaker::where("user_id", '=', $userId)
            ->where('access_id', '=', $accessId)
            ->first();
    }

    public function getAccessByName($name)
    {
        return Access::where('name', '=', $name)
            ->first();
    }

    public function setCurrentParticipants($accessId, $nbParticipants)
    {
        return Access::where('access_id', '=', $accessId)
            ->update(['nb_current_participants' => $nbParticipants]);
    }

    private function deleteAccessByCongress($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->delete();
    }


    public function getAllAccessByCongress($congressId, $showInRegister, $relations)
    {
        $accesses = Access::with($relations)
            ->where("congress_id", "=", $congressId)
            ->when($showInRegister != null, function ($query) use ($showInRegister) {
                return $query->where('show_in_register', '=', $showInRegister);
            })
            ->get();


        foreach ($accesses as $accesss) {
            $accesss->nb_participants = sizeof(array_filter(json_decode($accesss->participants, true), function ($item) {
                return sizeof($item['user_congresses']) > 0;
            }));
            $accesss->nb_confirmed = sizeof(array_filter(json_decode($accesss->participants, true), function ($item) {
                if (sizeof($item['user_congresses']) > 0) {
                    $isConfirmed = array_filter($item['user_congresses'], function ($q) {
                        return $q['will_be_present'] == 1;
                    });
                    return $isConfirmed;
                }
            }));
            $accesss->nb_presence = sizeof(array_filter(json_decode($accesss->participants, true), function ($item) {
                return sizeof($item['user_congresses']) > 0 && $item['pivot']['isPresent'] == 1;
            }));
        }
        return $accesses;
    }


    private function addSubAccess(Access $access, $sub)
    {
        $sub_access = new Access();
        $sub_access->name = $sub["name"];
        $sub_access->start_date = $sub["start_date"];
        $sub_access->end_date = $sub["end_date"];
        if (array_key_exists('description', $sub)) $sub_access->description = $sub['description'];
        if (array_key_exists('room', $sub)) $sub_access->room = $sub["room"];
        if (array_key_exists('topic_id', $sub)) $sub_access->topic_id = $sub["topic_id"];
        if (array_key_exists('seuil', $sub)) $sub_access->seuil = $sub['seuil'];
        if (array_key_exists('max_places', $sub)) $sub_access->max_places = $sub['max_places'];
        $sub_access->show_in_program = (!array_key_exists('show_in_program', $sub) || $sub['show_in_program']) ? 1 : 0;
        $sub_access->congress_id = $access->congress_id;
        $sub_access->parent_id = $access->access_id;
        $sub_access->save();

        if (array_key_exists('chair_ids', $sub) && count($sub['chair_ids']))
            $this->editChairs($sub_access->access_id, $sub['chair_ids']);

        if (array_key_exists('speakers', $sub) && count($sub['speakers']))
            $this->editSpeakers($sub_access->access_id, $sub['speakers']);

        if (array_key_exists('resource_ids', $sub) && count($sub['resource_ids']))
            $this->resourcesServices->editAccessResources($sub_access->access_id, $sub['resource_ids']);

        return $sub_access;
    }

    private function editSubAccess($old, $new)
    {
        if (array_key_exists('name', $new)) $old->name = $new["name"];
        if (array_key_exists('start_date', $new)) $old->start_date = $new["start_date"];
        if (array_key_exists('end_date', $new)) $old->end_date = $new["end_date"];
        if (array_key_exists('description', $new)) $old->description = $new['description'];
        if (array_key_exists('room', $new)) $old->room = $new["room"];
        if (array_key_exists('topic_id', $new)) $old->topic_id = $new["topic_id"];
        if (array_key_exists('seuil', $new)) $old->seuil = $new['seuil'];
        if (array_key_exists('max_places', $new)) $old->max_places = $new['max_places'];
        if (array_key_exists('show_in_program', $new)) $old->show_in_program = $new['show_in_program'] ? 1 : 0;
        $old->update();

        if (array_key_exists('chair_ids', $new) && count($new['chair_ids'])) {
            $this->editChairs($old->access_id, $new['chair_ids']);
        } else $this->removeAllChairs($old->access_id);
        if (array_key_exists('speakers', $new) && count($new['speakers'])) {
            $this->editSpeakers($old->access_id, $new['speaker_ids']);
        } else $this->removeAllSpeakers($old->access_id);

        if (array_key_exists('resource_ids', $new) && count($new['resource_ids'])) {
            $this->resourcesServices->editAccessResources($old->access_id, $new['resource_ids']);
        } else $this->resourcesServices->removeAllResources($old->access_id);
        return $old;

    }

    public function editVideoUrl($access, $isRecorder)
    {
        if (!$isRecorder) {
            $access->recorder_url = null;
        } else {

            $roomName = Utils::getRoomName($access->congress_id, $access->access_id);
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $res = $client->request('GET',

                UrlUtils::getBaseUrlDiscoveryRecording() . '/room/' . $roomName . '/discover');

            if ($res->getStatusCode() === 200) {
                $access->recorder_url = json_decode($res->getBody(), true)['recording'];
            }
        }

        $access->update();
    }

    // // la fonction existante getUserAccessByUser  nécessite accessId comme paramètre que je ne veux pas
    public function getAllAccessByUserId($userId)
    {
        return Access::whereHas('participants', function ($query) use ($userId) {
            $query->where('User.user_id', '=', $userId);
        })->get();
    }

    public function editAllAccessesStatus($congress_id, $status)
    {
        return Access::where('congress_id', '=', $congress_id)
            ->update(['status' => $status]);
    }

    public function editAccessStatus($access_id, $status)
    {
        return Access::where('access_id', '=', $access_id)
        ->update(['status' => $status]);
    }
    public function getScoresByAccess($congress_id, $access_id, $exclureInvitee = false)
    {
        $accessGame = collect(AccessGame::where('access_id', '=', $access_id)
                ->whereHas('user.user_congresses', function($query) use ($congress_id, $exclureInvitee) {
                    if($exclureInvitee){
                        $query->where('congress_id', '=', $congress_id);
                        $query->where('privilege_id', '<>', config('privilege.Invite'));
                    }
                })
                ->orderBy('score','desc')->with(['access' => function ($query) {
                    $query->select('Access.access_id', 'Access.name');
                }, 'user' => function ($query) {
                    $query->select('User.user_id','User.first_name', 'User.last_name');
                }])->get());
        $uniqueAccesses = $accessGame->unique('user_id');
        return $uniqueAccesses->values();
    }

    public function getScoresByCongress($congressId, $accesses, $exclureInvitee = false) 
    {
        $list = [];
        foreach( $accesses as $access) {
            array_push($list, $this->getScoresByAccess($congressId, $access->access_id, $exclureInvitee));
        }
        $values = collect($list)->collapse();
        $counted = $values->groupBy('user_id');
        $res = $counted->map(function ($item) {
                return [
                    'access_game_id'=> $item[0]->access_game_id,
                    'score'=> $item->sum('score'),
                    'user_id'=> $item[0]->user_id,
                    'access_id'=> $item[0]->access_id,
                    'user' => $item[0]->user,
                    'access' => $item[0]->access
                ];
        });
        return $res->values();
    }

    public function getGamesAccessesByCongress($congress_id)
    {
        return Access::where('congress_id', '=', $congress_id)
        ->where('access_type_id', '=', 4)
        ->get();
    }

    public function saveScoreGame($access_id, $request)
    {
        $access_game = new AccessGame();
        $access_game->access_id = $access_id;
        $access_game->user_id = $request->input('user_id');
        $access_game->score = $request->input('score');
        $access_game->save();
        return $access_game;
    }

    public function resetScore($access_id)
    {
        AccessGame::where('access_id', '=', $access_id)
        ->delete();
    }

    public function addAccessFromExcel($start_date, $end_date, $access_type_id, $congress_id, $moderator)
    {
        $access = new Access();
        $access->name = $moderator;
        $access->start_date = $start_date;
        $access->end_date = $end_date;
        $access->access_type_id = $access_type_id;
        $access->congress_id = $congress_id;
        $access->is_online = 1;
        $access->show_in_register = 1;
        $access->save();
        return $access;
    }

    
    public function getUserAccessesByCongressId($congress_id, $user_id)
    {
        return Access::where('congress_id', '=', $congress_id)
        ->where('is_online', '=', 1)
        ->with(['type'])
        ->whereHas('user_accesss' , function ($query) use ($user_id) {
            $query->where('user_id', '=', $user_id);
        })
        ->get();
    }
    
    public function getAccessesByCongressIdPginantion($congressId, $offset, $perPage, $search, $date, $startTime, $endTime, $isOnline, $myAccesses, $user_id)
    {
        $accesses = Access::with(['type','speakers','speaker'])
        ->whereNull('parent_id')
        ->where('congress_id', '=', $congressId)
        ->where(function ($query) use ($search) {
            if ($search !== '') {
                $query->whereRaw('lower(name) like (?)', ["%{$search}%"]);
                $query->orWhereRaw('lower(description) like (?)', ["%{$search}%"]);
                $query->orWhereRaw('(price) like (?)',  ["%{$search}%"]);
            }
        })->where(function ($query) use ($date, $startTime, $endTime) {
            if ($date != '' && $date != 'null')
                $query->whereDate('start_date', date($date));
            if ($startTime != '' && $startTime != 'null')
                $query->whereTime('start_date', '>=', $startTime);
            if ($endTime != '' && $endTime != 'null')
                $query->whereTime('end_date', '<=', $endTime);
            
        })
        ->where(function ($query) use ($isOnline, $user_id, $myAccesses) {
            if ($isOnline != ''&& $isOnline != 'null')
                $query->where('is_online', '=', $isOnline);
            if ($myAccesses == 1) {
                $query->whereHas('user_accesss' , function ($q) use ($user_id) {
                    $q->where('user_id', '=', $user_id);
            });
            } 
        })
        ->offset($offset)->paginate($perPage);
        
        return $accesses;
    }

    public function getAllPresentUserAccessByAccessId($accessId)
    {
        return UserAccess::where('access_id', '=', $accessId)
            ->where('isPresent', '=', 1)
            ->get();
    }

    public function updateUserAccessDuration($accessId, $accessEndDate)
    {
        $userAccesses = $this->getAllPresentUserAccessByAccessId($accessId);
        foreach($userAccesses as $userAccess) {
            $accessPresence  = $this->getAccessPresence($userAccess->user_id, $userAccess->access_id);
            if ($accessPresence) {
                if ($accessPresence->left_at) {
                    $timeDiff = Utils::diffMinutes($accessPresence->left_at, $accessPresence->entered_at);
                } else {
                    $timeDiff = Utils::diffMinutes($accessEndDate, $accessPresence->entered_at);
                }
            }
            $userAccess->duration = $userAccess->duration + $timeDiff;
            $userAccess->update();
        }
    }

    public function getAccessPresence($userId, $accessId)
    {
        return AccessPresence::where('user_id', '=', $userId)
        ->where('access_id', '=', $accessId)
        ->first();
    }
}
