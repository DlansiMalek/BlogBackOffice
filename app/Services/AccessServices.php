<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;


use App\AccessChair;
use App\Models\Access;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AccessServices
{


    public function addAccess($congress_id, $request)
    {
        $access = new Access();
        $access->name = $request->input("name");
//        $access->start_date = $request->input("start_date");
//        $access->end_date = $request->input("end_date");
        $access->access_type_id = $request->input('access_type_id');
        if ($request->has('price')) $access->price = $request->input("price");
        if ($request->has('packless')) $access->packless = $request->input("packless") ? 1 : 0;
        if ($request->has('description')) $access->description = $request->input("description");
        if ($request->has('room')) $access->room = $request->input("room");
        if ($request->has('topic_id')) $access->topic_id = $request->input("topic_id");
        if ($request->has('seuil')) $access->seuil = $request->input('seuil');
        if ($request->has('max_places')) $access->max_places = $request->input('max_places');
        $access->show_in_program = (!$request->has('show_in_program') || $request->input('show_in_program')) ? 1 : 0;
        $access->congress_id = $congress_id;
        $access->save();
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
        foreach ($chairs as $chair){
            $access_chair = new AccessChair();
            $access_chair->access_id = $access['access_id'];
            $access_chair->user_id = $chair;
            $access_chair->save();
        }
    }

    public function addSpeakers(Access $access, $speakers)
    {
        foreach ($speakers as $speaker){
            $access_chair = new AccessChair();
            $access_chair->access_id = $access['access_id'];
            $access_chair->user_id = $speaker;
            $access_chair->save();
        }
    }

    public function addSubAccesses(Access $access, $sub_accesses)
    {
        foreach ($sub_accesses as $sub){
            $this->addSubAccess($access,$sub);
        }
    }

    public function getAccessById($access_id)
    {
        return Access::with(['speakers','chairs','sub_accesses','topic','resources','type'])->find($access_id);
    }


    private function deleteAccessByCongress($congressId)
    {
        return Access::where('congress_id', '=', $congressId)
            ->delete();
    }


    public
    function getAllAccessByCongress($congressId)
    {
        return Access::with(['participants'])
            ->where("congress_id", "=", $congressId)
            ->get();
    }

    function getAccessesByCongressId($intuitive, $congressId)
    {
        return $intuitive ?
            Access::where("congress_id", '=', $congressId)
                ->where('intuitive', '=', true)
                ->get() :
            Access::where("congress_id", '=', $congressId)
                ->where('intuitive', '=', false)
                ->get();

    }

    private function addSubAccess(Access $access, $sub)
    {
        $sub_access = new Access();
        $sub_access->name = $sub["name"];
//        $sub_access->start_date = $sub["start_date"];
//        $sub_access->end_date = $sub["end_date"];
        $sub_access->access_type_id = $sub['access_type_id'];
        if (array_key_exists('description',$sub)) $sub_access->description = $sub['description'];
        if (array_key_exists('room',$sub)) $sub_access->room = $sub["room"];
        if (array_key_exists('topic_id',$sub)) $sub_access->topic_id = $sub["topic_id"];
        if (array_key_exists('seuil',$sub)) $sub_access->seuil = $sub['seuil'];
        if (array_key_exists('max_places',$sub)) $sub_access->max_places = $sub['max_places'];
        $sub_access->show_in_program = (!array_key_exists('show_in_program',$sub) || $sub['show_in_program']) ? 1 : 0;
        $sub_access->congress_id = $access->congress_id;
        $sub_access->parent_id = $access->access_id;
        $sub_access->save();
        return $sub_access;
    }
}
