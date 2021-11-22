<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:42
 */

namespace App\Services;

use App\Models\AccessVote;
use App\Models\Menu;
use App\Models\MenuChildren;
use App\Models\MenuChildrenOffre;
use App\Models\Offre;
use App\Models\PaymentAdmin;
use App\Models\PrivilegeMenuChildren;
use App\Models\VoteScore;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @property \GuzzleHttp\Client client
 */
class OffreServices
{
    public function getAllOffres()
    {
        return Offre::with(['admin', 'type'])
            ->get();
    }

    public function getOffreById($offre_id)
    {
        return Offre::where('offre_id', '=', $offre_id)->first();

    }

    public function getMenusByOffre($offre_id)
    {
        return Menu::whereHas('menu_children_offre', function ($query) use ($offre_id) {
            $query->where('offre_id', '=', $offre_id)
                ->orderBy('menu_id');
        }
        )->with(['menu_children' => function ($query) use ($offre_id) {
            $query->whereHas('menu_children_offre', function ($query) use ($offre_id) {
                $query->where('offre_id', '=', $offre_id);
            });
        }
        ])->get();
    }

    public function getActiveOffreByAdminId($admin_id)
    {
        return Offre::where('admin_id', '=', $admin_id)->where('status', '=', 1)
            ->with(['menu_children_offre' => function ($query) {
                $query->orderBy('menu_id')->orderBy('menu_children_id')
                    ->with(['menu_children', 'menu']);
            }
            ])->first();
    }

    public function addOffre($request)
    {
        $offre = new Offre();
        $offre->name = $request->input('name');
        $offre->start_date = $request->input('start_date');
        $offre->end_date = $request->input('end_date');
        $offre->value = $request->input('value');
        $offre->status = 1;
        $offre->offre_type_id = $request->input('offre_type_id');
        $offre->is_mail_pro = $request->input('is_mail_pro');
        $offre->admin_id = $request->input('admin_id');
        $offre->save();

        $this->addPayment($request->input('admin_id'), $offre);
        $this->addAllMenuChildrenOffre($request['menus'], $offre->offre_id);

        return $offre;
    }

    public function editOffre($offre, $request)
    {
        $offre->name = $request->input('name');
        $offre->start_date = $request->input('start_date');
        $offre->end_date = $request->input('end_date');
        $offre->value = $request->input('value');
        $offre->offre_type_id = $request->input('offre_type_id');
        $offre->admin_id = $request->input('admin_id');
        $offre->is_mail_pro = $request->input('is_mail_pro');
        $offre->update();

        $this->deleteAllOffreMenus($offre->offre_id);
        $this->addAllMenuChildrenOffre($request['menus'], $offre->offre_id);

        return $offre;
    }

    public function deactivateOffre($offre)
    {
        $offre->status = 0;
        $offre->update();
    }

    public function getOffreByCongressId($congress_id)
    {
        return Offre::where('status', '=', 1)
            ->join('Admin_Congress', function ($join) use ($congress_id) {
                $join->on('Admin_Congress.admin_id', '=', 'Offre.admin_id')
                    ->where('congress_id', '=', $congress_id)
                    ->where('privilege_id', '=', config('privilege.Admin'));
            })->first();
    }

    public function addPayment($admin_id, $offre)
    {
        $paymentAdmin = new PaymentAdmin();
        $paymentAdmin->isPaid = 0;
        $paymentAdmin->admin_id = $admin_id;
        $paymentAdmin->offre_id = $offre->offre_id;
        if ($offre->type_id == 1 || $offre->type_id == 4) {
            $paymentAdmin->price = $offre->value;
        } else {
            $paymentAdmin->price = 0;
        }
        $paymentAdmin->save();
    }

    public function getAllMenu()
    {
        return Menu::with(['menu_children'])->get();
    }

    public function addAllMenuChildrenOffre($menus, $offre_id)
    {
        foreach ($menus as $new) {
            $menu_id = $new['menu_id'];
            $menuChildren = $new['menu_children_ids'];
            if ($menuChildren == []) {
                $this->handleMenuChildrenOffre($offre_id, $menu_id);
            } else {
                foreach ($menuChildren as $child) {
                    $this->handleMenuChildrenOffre($offre_id, $menu_id, $child);
                }
            }
        }
    }

    public function handleMenuChildrenOffre($offre_id, $menu_id, $child=null) {
        if (!$exist = $this->getMenuChildrenOffreByIds($offre_id, $menu_id, $child)) {
            $this->addMenuChildrenOffre($offre_id, $menu_id, $child);
        }
    }

    public function addMenuChildrenOffre($offre_id, $menu_id, $menu_children_id = null)
    {
        $menuChildrenOffre = new MenuChildrenOffre();
        $menuChildrenOffre->offre_id = $offre_id;
        $menuChildrenOffre->menu_children_id = $menu_children_id;
        $menuChildrenOffre->menu_id = $menu_id;
        $menuChildrenOffre->save();
    }

    public function getMenuChildrenOffreByIds($offre_id, $menu_id, $menu_children_id = null)
    {
        return MenuChildrenOffre::where('offre_id', '=', $offre_id)
            ->where('menu_id', '=', $menu_id)
            ->where('menu_children_id', '=', $menu_children_id)
            ->first();
    }

    public function deleteAllOffreMenus($offre_id)
    {
        return MenuChildrenOffre::where('offre_id', '=', $offre_id)
            ->delete();
    }


    public function getPrivilegeMenuChildrenByIds($privilege_id, $congress_id, $menu_id, $menu_children_id = null)
    {
        return PrivilegeMenuChildren::where('privilege_id', '=', $privilege_id)
            ->where('congress_id', '=', $congress_id)
            ->where('menu_id', '=', $menu_id)
            ->where('menu_children_id', '=', $menu_children_id)
            ->first();
    }

    public function getMenusByPrivilegeByCongress($congress_id, $privilege_id)
    {
        return Menu::whereHas('privilege_menu_children', function ($query) use ($congress_id, $privilege_id) {
            $query->where('privilege_id', '=', $privilege_id)
                ->where('congress_id', '=', $congress_id)
                ->orderBy('menu_id');
        }
        )->with(['menu_children'=> function ($query) use ($congress_id, $privilege_id) {
            $query->whereHas('privilege_menu_children', function ($query) use ($congress_id, $privilege_id) {
                $query->where('privilege_id', '=', $privilege_id)
                    ->where('congress_id', '=', $congress_id);
            });
        }
        ])->get();
    }

    public function editPrivilegeMenuChildren($menus, $privilege_id, $congress_id)
    {
        $this->deleteAllPrivilegeMenuChildren($congress_id, $privilege_id);
        $this->addAllPrivilegeMenuChildren($menus, $privilege_id, $congress_id);
    }

    public function deleteAllPrivilegeMenuChildren($congress_id, $privilege_id)
    {
        return PrivilegeMenuChildren::where('privilege_id', '=', $privilege_id)
            ->where('congress_id', '=', $congress_id)
            ->delete();
    }

    public function addAllPrivilegeMenuChildren($menus, $privilege_id, $congress_id)
    {
        foreach ($menus as $new) {
            $menu_id = $new['menu_id'];
            $menuChildren = $new['menu_children_ids'];
            if ($menuChildren == []) {
                $this->handlePrivilegeMenuChildren($privilege_id, $congress_id, $menu_id);
            } else {
                foreach ($menuChildren as $child) {
                    $this->handlePrivilegeMenuChildren($privilege_id, $congress_id, $menu_id, $child);
                }
            }
        }
    }

    public function handlePrivilegeMenuChildren($privilege_id, $congress_id, $menu_id, $child = null) {
        if (!$exsit = $this->getPrivilegeMenuChildrenByIds($privilege_id, $congress_id, $menu_id, $child)) {
            $this->addPrivilegeMenuChildren($privilege_id, $congress_id, $menu_id, $child);
        }
    }

    public function addPrivilegeMenuChildren($privilege_id, $congress_id, $menu_id, $menu_children_id = null)
    {
        $privilegeMenuChildren = new PrivilegeMenuChildren();
        $privilegeMenuChildren->congress_id = $congress_id;
        $privilegeMenuChildren->privilege_id = $privilege_id;
        $privilegeMenuChildren->menu_id = $menu_id;
        $privilegeMenuChildren->menu_children_id = $menu_children_id;
        $privilegeMenuChildren->save();
    }

}
