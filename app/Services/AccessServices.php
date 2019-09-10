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
use App\Models\AccessSpeaker;
use App\Models\AccessType;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        if ($request->has('price')) $access->price = $request->input("price");
        if ($request->has('packless')) $access->packless = $request->input("packless") ? 1 : 0;
        if ($request->has('description')) $access->description = $request->input("description");
        if ($request->has('room')) $access->room = $request->input("room");
        if ($request->has('topic_id')) $access->topic_id = $request->input("topic_id");
        if ($request->has('seuil')) $access->seuil = $request->input('seuil');
        if ($request->has('max_places')) $access->max_places = $request->input('max_places');
        $access->show_in_program = (!$request->has('show_in_program') || $request->input('show_in_program')) ? 1 : 0;

        if ($request->has('show_in_register'))
            $access->show_in_register = $request->input('show_in_register');

        if ($request->has('with_attestation'))
            $access->with_attestation = $request->input('with_attestation');


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

        if ($request->has('show_in_register'))
            $access->show_in_register = $request->input('show_in_register');

        if ($request->has('with_attestation'))
            $access->with_attestation = $request->input('with_attestation');


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
            $access_chair = new AccessChair();
            $access_chair->access_id = $access['access_id'];
            $access_chair->user_id = $chair;
            $access_chair->save();
        }
    }

    public function addSpeakers(Access $access, $speakers)
    {
        foreach ($speakers as $speaker) {
            $access_speaker = new AccessSpeaker();
            $access_speaker->access_id = $access['access_id'];
            $access_speaker->user_id = $speaker;
            $access_speaker->save();
        }
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
                'sub_accesses.speakers', 'sub_accesses.chairs', 'sub_accesses.topic', 'sub_accesses.resources', 'sub_accesses.type'])
            ->find($access_id);
    }

    public function getByCongressId($congress_id)
    {
        return Access::with(['speakers', 'chairs', 'topic', 'resources', 'type',
            'sub_accesses.speakers', 'sub_accesses.chairs', 'sub_accesses.topic', 'sub_accesses.resources', 'sub_accesses.type'])
            ->whereNull('parent_id')
            ->where('congress_id', '=', $congress_id)
            ->get();
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

    public function getAllAccessByRegisterParams($congress_id, $showInRegister)
    {
        return Access::where(function ($query) use ($showInRegister) {
            if ($showInRegister != null) {
                $query->where('show_in_register', $showInRegister);
            }
        })
            ->whereNull('parent_id')
            ->where('congress_id', '=', $congress_id)
            ->get();
    }


    private function deleteAccessByCongress($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->delete();
    }


    public function getAllAccessByCongress($congressId)
    {
        return Access::with(['participants'])
            ->where("congress_id", "=", $congressId)
            ->get();
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
}
