<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:36
 */

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OffreServices;
use App\Services\PrivilegeServices;
use App\Services\UrlUtils;
use Illuminate\Http\Request;


class OffreController extends Controller
{

    protected $adminServices;
    protected $congressServices;
    protected $mailServices;
    protected $offreServices;
    protected $privilegeServices;

    function __construct(AdminServices $adminServices,
                         MailServices $mailServices,
                         OffreServices $offreServices,
                         CongressServices $congressServices,
                         PrivilegeServices $privilegeServices)
    {
        $this->adminServices = $adminServices;
        $this->mailServices = $mailServices;
        $this->congressServices = $congressServices;
        $this->offreServices = $offreServices;
        $this->privilegeServices = $privilegeServices;
    }

    public function getAllOffres()
    {
        $offres = $this->offreServices->getAllOffres();
        return response()->json($offres, 200);
    }

    public function getOffreById($offre_id)
    {
        $offre = $this->offreServices->getOffreById($offre_id);
        $menus = $this->offreServices->getMenusByOffre($offre_id);
        return response()->json(['offre' => $offre, 'menus' => $menus], 200);
    }

    public function addOffre(Request $request)
    {
        if (!$request->has(['name', 'value', 'start_date', 'end_date', 'offre_type_id', 'admin_id', 'menus'])) {
            return response()->json(['message' => 'bad request'], 400);
        }
        if (!$admin = $this->adminServices->getAdminById($request->input('admin_id'))) {
            return response()->json(['message' => 'admin not found'], 404);
        }
        if ($oldOffre = $this->offreServices->getActiveOffreByAdminId($request->input('admin_id'))) {
            $this->offreServices->deactivateOffre($oldOffre);
        }
        if (!$mailTypeAdmin = $this->mailServices->getMailTypeAdmin('create_offre')) {
            return response()->json(['message' => 'Mail type not found'], 400);
        }

        $offre = $this->offreServices->addOffre($request);

        $mailAdmin = $this->mailServices->getMailAdmin($mailTypeAdmin->mail_type_admin_id);
        if ($mailAdmin) {
            $paymentLink = UrlUtils::getUrlEventizerWeb() . "/#/auth/admin/" . $admin->admin_id . "/upload-payement";
            $this->mailServices->sendMAil($this->adminServices->renderMail($mailAdmin->template, $admin, null, null, null, $paymentLink), $admin, null, $mailAdmin->object, false);
        }
        return response()->json(['messsage' => 'offre created successfully', 'offre' => $offre], 200);
    }

    public function editOffre(Request $request, $offre_id)
    {
        if (!$request->has(['name', 'value', 'start_date', 'end_date', 'offre_type_id', 'admin_id']))
            return response()->json(['message' => 'bad request'], 400);

        if (!$offre = $this->offreServices->getOffreById($offre_id))
            return response()->json(['message' => 'offre not found'], 404);

        $offre = $this->offreServices->editOffre($offre, $request);
        return response()->json(['messsage' => 'offre edited successfully', 'offre' => $offre], 200);
    }

    public function getAllMenu()
    {
        $menus = $this->offreServices->getAllMenu();
        return response()->json($menus, 200);
    }

    public function addPrivilegeMenuChildren( $congress_id, $privilege_id, Request $request)
    {
        if (!$privilege = $this->privilegeServices->getPrivilegeById($privilege_id)) {
            return response()->json(['message' => 'privilege not found'], 404);
        }
        if (!$congress = $this->congressServices->isExistCongress($congress_id)) {
            return response()->json(['message' => 'congress not found'], 404);
        }
        $this->offreServices->addAllPrivilegeMenuChildren($request->input('menus'),  $privilege_id, $congress_id);
        return response()->json(['message' => 'success!']);
    }

    public function getMenusByPrivilegeByCongress($congress_id, $privilege_id)
    {
        if (!$privilege = $this->privilegeServices->getPrivilegeById($privilege_id)) {
            return response()->json(['message' => 'privilege not found'], 404);
        }
        if (!$congress = $this->congressServices->isExistCongress($congress_id)) {
            return response()->json(['message' => 'congress not found'], 404);
        }
        $menus = $this->offreServices->getMenusByPrivilegeByCongress($congress_id, $privilege_id);
        return response()->json(['menus' => $menus], 200);
    }

    public function editPrivilegeMenuChildren( $congress_id, $privilege_id, Request $request)
    {
        if (!$privilege = $this->privilegeServices->getPrivilegeById($privilege_id)) {
            return response()->json(['message' => 'privilege not found'], 404);
        }
        if (!$congress = $this->congressServices->isExistCongress($congress_id)) {
            return response()->json(['message' => 'congress not found'], 404);
        }
        $this->offreServices->editPrivilegeMenuChildren($request->input('menus'),  $privilege_id, $congress_id);
        return response()->json(['message' => 'success!']);
    }

}
