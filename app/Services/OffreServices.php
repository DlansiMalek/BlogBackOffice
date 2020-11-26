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
        return Offre::where('offre_id', '=', $offre_id)
            ->with(['menu_children_offre' => function ($query) use ($offre_id) {
                $query->where('offre_id', '=', $offre_id)
                    ->orderBy('menu_id')
                ->with(['menu_children', 'menu']);
            }
            ])->first();

    }

    public function getActiveOffreByAdminId($admin_id)
    {
        return Offre::where('admin_id', '=', $admin_id)->where('status', '=', 1)
            ->with(['menu_children_offre' => function ($query) {
                $query->orderBy('menu_id')
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
            ->join('admin_congress', function ($join) use ($congress_id) {
                $join->on('admin_congress.admin_id', '=', 'offre.admin_id')
                    ->where('congress_id', '=', $congress_id)
                    ->where('privilege_id', '=', 1);
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
            if ($menu_id == 14) {
                if(!$exsit = $this->getMenuChildrenOffreByIds($offre_id, $menu_id)) {
                    $this->addMenuChildrenOffre($offre_id, $menu_id);
                }
            } else {
                foreach ($menuChildren as $child) {
                    if(!$exsit = $this->getMenuChildrenOffreByIds($offre_id, $menu_id, $child)){
                        $this->addMenuChildrenOffre($offre_id, $menu_id, $child);
                    }
                }
            }
        }
    }

    public function addMenuChildrenOffre($offre_id, $menu_id, $menu_children_id = null) {
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

}
