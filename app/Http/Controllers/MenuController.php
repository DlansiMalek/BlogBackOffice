<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\MenuServices;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected $menuServices;
    protected $adminServices;
    public function __construct(MenuServices $menuServices,
        AdminServices $adminServices
    ) {
        $this->menuServices = $menuServices;
    }
    public function getMenus($show_after_reload)
    {
        return $this->menuServices->getAllMenus($show_after_reload);
    }
    public function setMenus(Request $request,$show_after_reload)
    {
        $this->menuServices->setMenus($request,$show_after_reload);
        return $this->menuServices->getAllMenus($show_after_reload);
    }
}
