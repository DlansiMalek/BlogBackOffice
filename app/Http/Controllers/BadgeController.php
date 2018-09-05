<?php

namespace App\Http\Controllers;


use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use Illuminate\Http\Request;


class BadgeController extends Controller
{

    protected $congressServices;
    protected $adminServices;
    protected $badgeServices;

    function __construct(CongressServices $congressServices, AdminServices $adminServices, BadgeServices $badgeServices)
    {
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->badgeServices = $badgeServices;
    }

    function uploadBadgeToCongress(Request $request, $congressId)
    {
        $file = $request->file('file_data');
        $chemin = config('media.badge-medias');
        $path = $file->store($chemin);


        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(["error" => "congress not found"]);
        } else {
            $badge = $this->badgeServices->getBadgeByCongress($congressId);
        }
        $badge = $this->badgeServices->uploadBadge($badge, $path, $congressId);

        return response()->json($badge, 200);
    }

    function validerBadge(Request $request, $congressId)
    {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["error" => "congress not found"]);
        }
        $this->badgeServices->validerBadge($request, $congressId);

        return response()->json(["message" => "validation success"]);

    }

    function apercuBadge()
    {
        return $this->badgeServices->impressionBadge();
    }

    function affectBadgeToCongress($congressId, Request $request)
    {
        $badgeIdGenerator = $request->input('badgeIdGenerator');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if ($badge = $this->badgeServices->getBadgeByCongress($congressId)) {
            $badge->delete();
        }
        $this->badgeServices->validerBadge($congressId, $badgeIdGenerator);
        return response($this->badgeServices->getBadgeByCongress($congressId));
    }


}
