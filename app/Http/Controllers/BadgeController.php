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
        // Affectation Badge to Congress
        if ($badge = $this->badgeServices->getBadgeByCongressAndPrivilege($congressId, $request->input('privilegeId'))) {
            $badge->badge_id_generator = $badgeIdGenerator;
            $badge->update();
        } else {
            $this->badgeServices->validerBadge($congressId, $badgeIdGenerator, $request->input('privilegeId'));
        }
        return response($this->badgeServices->getBadgeByCongressAndPrivilege($congressId, $request->input('privilegeId')));
    }


    function affectAttestationDivers($congressId, Request $request)
    {
        $attestationGenerator = $request->input('badgeIdGenerator');
        $attestationTypeId = $request->input('attestationTypeId');

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        // Affectation Badge to Congress
        if ($attestation = $this->badgeServices->getAttestationByCongressAndType($congressId, $attestationTypeId)) {
            $attestation->attestation_generator_id = $attestationGenerator;
            $attestation->update();
        } else {
            $this->badgeServices->validerAttestationType($congressId, $attestationGenerator, $attestationTypeId);
        }

        return response()->json($this->badgeServices->getAttestationByCongressAndType($congressId, $attestationTypeId));

    }

    function affectAttestationToCongress($congressId, $accessId, Request $request)
    {
        $attesationIdGenerator = $request->input('badgeIdGenerator');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if ($accessId) {
            if ($attestationAccess = $this->badgeServices->getAttestationByCongressAndAccess($accessId)) {
                $attestationAccess->delete();
            }
            $this->badgeServices->validerAttestationAccess($accessId, $attesationIdGenerator);
            return response($this->badgeServices->getAttestationByCongressAndAccess($accessId));
        } else {
            // Affectation Attestation to Congress
            if ($attesation = $this->badgeServices->getAttestationByCongress($congressId)) {
                if ($request->input('blank') == 1) {
                    $attesation->attestation_generator_id_blank = $attesationIdGenerator;
                } else {
                    $attesation->attestation_generator_id = $attesationIdGenerator;
                }
                $attesation->update();
            } else {
                $this->badgeServices->validerAttestation($congressId, $attesationIdGenerator, $request->input('blank'));
            }
            return response($this->badgeServices->getAttestationByCongress($congressId));
        }

    }


}
