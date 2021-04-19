<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuServices
{
    public function addMenu(Request $request)
    {
        $menu = new Menu();
        $menu->key = $request->input("key");
        $menu->icon = $request->input("icon");
        if ($request->has('reload')) $menu->reload = $request->input("reload");
        if ($request->has('url')) $menu->url = $request->input("url");
        $menu-> save();
        return $menu;
    }
    public function editMenu($menu, Request $request)
    {
        if ($request->has('key')) $menu->key = $request->input("key");
        if ($request->has('icon')) $menu->icon = $request->input("icon");
        if ($request->has('reload')) $menu->reload = $request->input("reload");
        if ($request->has('url')) $menu->url = $request->input("url");
        $menu->update();
        return $menu;
    }
    public function getById($menuId)
    {
        return Menu::find($menuId);
    }
    public function getAllMenus()
    {
        return Menu::all();
    }
}
