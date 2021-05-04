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
    public function getMenus()
    {
        return $this->menuServices->getAllMenus();
    }
    public function setMenus(Request $request)
    {
        $this->menuServices->setMenus($request);
        return $this->menuServices->getAllMenus();
    }
}
