<?php

namespace App\Http\Controllers;


use App\Models\AdminCongress;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\PrivilegeServices;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class BadgeController extends Controller
{

    protected $congressServices;
    protected $adminServices;
    protected $badgeServices;
    protected $privilegeServices;

    function __construct(CongressServices $congressServices, AdminServices $adminServices, BadgeServices $badgeServices, PrivilegeServices $privilegeServices)
    {
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->badgeServices = $badgeServices;
        $this->privilegeServices = $privilegeServices;
    }

    function affectBadgeToCongress($congressId, Request $request)
    {
        $badgeIdGenerator = $request->input('badgeIdGenerator');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$this->privilegeServices->checkValidPrivilege($request->input('privilegeId'))) {
            return response(['error' => "invalid privilege"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=(AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            // Affectation Badge to Congress
            if ($badge = $this->badgeServices->getBadgeByCongressAndPrivilegeBadgeAndIdGenerator($congressId, $request->input('privilegeId'), $badgeIdGenerator)) {
                $this->badgeServices->updateOrCreateBadgeParams($badge->badge_id, $request->input('keys'), true);
                $badge->enable = 1;
                $badge->update();
            } else {
                $badge = $this->badgeServices->validerBadge($congressId, $badgeIdGenerator, $request->input('privilegeId'));
                $this->badgeServices->updateOrCreateBadgeParams($badge->badge_id, $request->input('keys'), false);
            }
            $badges = $this->badgeServices->getBadgesByCongressAndPrivilege($congressId, $request->input('privilegeId'));
            $this->badgeServices->activateBadgeByCongressByPriviledge($badges, $badgeIdGenerator);
            return response()->json($this->congressServices->getMinimalCongress($congressId));
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    function affectNewBadgeToCongress($congressId, Request $request) {
        $badgeIdGenerator = $request->input('badgeIdGenerator');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$this->privilegeServices->checkValidPrivilege($request->input('privilegeId'))) {
            return response(['error' => "invalid privilege"], 404);
        }
        if ($badge = $this->badgeServices->getBadgeByCongressAndPrivilegeBadgeAndIdGenerator($congressId, $request->input('privilegeId'),$badgeIdGenerator)) {

            $this->badgeServices->updateOrCreateBadgeParams($badge->badge_id,$request->input('keys'), true);
        }

        if ($badges = $this->badgeServices->getBadgesByCongressAndPrivilege($congressId, $request->input('privilegeId'))) {
            $badges->update(['enable' => 0,]);
        }
        $this->badgeServices->validerBadge($congressId, $badgeIdGenerator, $request->input('privilegeId'));
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
        $privilegeId = $request->input("privilegeId");
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if ($accessId) {
            if ($attestationAccess = $this->badgeServices->getAttestationByCongressAndAccess($accessId, $privilegeId)) {
                $attestationAccess->delete();
            }
            $this->badgeServices->validerAttestationAccess($accessId,$privilegeId, $attesationIdGenerator);
            return response($this->badgeServices->getAttestationByCongressAndAccess($accessId, $privilegeId));
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

    function getBadgesByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=(AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            if ($adminCongress->privilege_id != 1) {
                return response()->json(['error' => 'forbidden'], 403);
            }
            $badges = $this->badgeServices->getBadgesByCongress($congressId);
            return response($badges, 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }
    }

    function activateBadgeByCongressByPrivilege($congressId,Request $request) {
        $badgeIdGenerator = $request->input('badgeIdGenerator');
        $privilegeId = $request->input('privilegeId');
        if (!($privilege = $this->privilegeServices->checkValidPrivilege($privilegeId))) {
            return response(['error' => $privilegeId], 404);
        }
        if (!$badge=$this->badgeServices->getBadgeByCongressAndPrivilegeBadgeAndIdGenerator($congressId,$privilegeId,$badgeIdGenerator)) {
            return response(['error' => "badge not found"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=(AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            if ($adminCongress->privilege_id != 1) {
                return response()->json(['error' => 'forbidden'], 403);
            }
            $badges = $this->badgeServices->getBadgesByCongressAndPrivilege($congressId, $privilegeId);
            $response = $this->badgeServices->activateBadgeByCongressByPriviledge($badges,$badgeIdGenerator);
            return response(['response' =>$response], 200);
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }
    }

}
