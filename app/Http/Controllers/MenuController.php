<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuServices;
use App\Services\AdminServices;


class MenuController extends Controller
{
    protected $menuServices;
    protected $adminServices;
    public function __construct(MenuServices $menuServices,
        AdminServices $adminServices
    ) {
        $this->menuServices = $menuServices;
        $this->adminServices = $adminServices;
    }


    public function addMenu(Request $request)
    {
        if (!$request->has(['key', 'icon'])) {
            return response()->json(['response' => 'invalid request',
                'required fields' => ['key', 'icon']], 400);
        }

      /*  if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['response' => 'admin not found'], 404);
        }*/

        $menu = $this->menuServices->addMenu($request);
        return response()->json($menu);
    }
    public function editMenu(Request $request, $menuId)
    {
        if (!$menu = $this->menuServices->getById($menuId))
            return response()->json(['message' => 'access not found'], 404);

            $menu = $this->menuServices->editMenu($menu, $request);
            
            return $this->menuServices->getById($menu->menu_id);

    }
    public function getMenus()
    {
        return $this->menuServices->getAllMenus();
    }
}
