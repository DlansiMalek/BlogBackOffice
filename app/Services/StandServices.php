<?php

namespace App\Services;

use App\Models\Stand;


class StandServices
{

    public function getStands($congress_id, $name = null)
    {
        return Stand::where(function ($query) use ($name) {
            if ($name) {
                $query->where('name', '=', $name);
            }
        })
            ->with(['docs'])
            ->where('congress_id', '=', $congress_id)->get();
    }

    public function getStandById($stand_id)
    {
        return Stand::where('congress_id', '=', $stand_id)->get();
    }

    public function editStands($congress_id, $stand_id, $url_streaming)
    {
        $stand = Stand::where('congress_id', '=', $congress_id)
            ->where('stand_id', '=', $stand_id)->first();
        $stand->url_streaming = $url_streaming;
        $stand->update();
        return $stand;
    }

    public function getDocsByStands($stands)
    {
        $res = array();

        foreach ($stands as $stand) {
            foreach ($stand->docs as $doc) {
                array_push(
                    $res,
                    array(
                        "stand" => $stand->name,
                        "path" => UrlUtils::getBaseUrl() . '/resource/' . $doc->path,
                        "filename" => $doc->path,
                        "version" => $doc->pivot->version
                    )
                );
            }
        }
        return $res;
    }

    public function getUrlsByStandsAndAccess($stands, $accesses)
    {
        $res = array();

        foreach ($stands as $stand) {
            array_push(
                $res,
                array(
                    "channel_name" => $stand->name,
                    "url" => $stand->url_streaming
                )
            );
        }

        foreach ($accesses as $access) {
            array_push(
                $res,
                array(
                    "channel_name" => $access->name,
                    "url" => $access->url_streaming
                )
            );
        }
        return $res;
    }

    public function modifyAllStatusStand($congressId, $status)
    {
        return Stand::where('congress_id', '=', $congressId)
            ->update(['status' => $status]);
    }

    public function getStatusGlobalStand($stands)
    {
        foreach ($stands as $stand) {
            if ($stand->status == 1) {
                return true;
            }
        }
        return false;
    }
}
