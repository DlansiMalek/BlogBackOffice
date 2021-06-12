<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuChildren;

class MenuServices
{

    public function getAllMenus()
    {
        return Menu::with(['menu_children' => function ($query) {
            $query->orderBy('index');

        }])->orderBy('index')->get();

    }
    public function getMenuChildrenByMenu($menuId)
    {
        return MenuChildren::where('menu_id', '=', $menuId)->get();
    }

    public function addMenuChildren($valueRequest, $menuChildren = null, $idMenu)
    {

        if (!$menuChildren) {
            $menuChildren = new MenuChildren();
            $menuExist = false;
        } else {
            $menuExist = true;
        }

        $menuChildren->menu_children_id = $valueRequest['menu_children_id'];
        $menuChildren->key = $valueRequest["key"];
        $menuChildren->icon = $valueRequest["icon"];
        $menuChildren->url = $valueRequest["url"];
        $menuChildren->menu_id = $idMenu;
        $menuChildren->index = $valueRequest["index"];

        if (!$menuExist) {
            $menuChildren->save();
        } else {
            $menuChildren->update();
        }

    }
    public function addMenu($new, $menu = null)
    {

        if (!$menu) {$menu = new Menu();
            $menuExist = false;
        } else {
            $menuExist = true;
        }

        $menu->menu_id = $new["menu_id"];
        $menu->key = $new["key"];
        $menu->url = $new["url"];
        $menu->icon = $new["icon"];
        $menu->index = $new["index"];

        if (!$menuExist) {
            $menu->save();
        } else {
             $menu->update();
            }
    }
    public function setMenus($newMenu)
    {
        $oldMenu = $this->getAllMenus();
        foreach ($oldMenu as $old) {
            $exists = false;
            foreach ($newMenu->all() as $new) {
                if ($old->menu_id == $new['menu_id']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $old->delete();
            }

        }

        foreach ($newMenu->all() as $new) {
            $menu = null;
            foreach ($oldMenu as $old) {
                if ($old->menu_id == $new['menu_id']) {
                    $menu = $old;
                    break;
                }
            }
            $this->addMenu($new, $menu);
            $oldChildrens = $this->getMenuChildrenByMenu($new['menu_id']);
            foreach ($oldChildrens as $oldChildren) {
                $exists = false;
                foreach ($new["menu_children"] as $newChildren) {
                    if ($newChildren['menu_children_id'] == $oldChildren->menu_children_id) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $oldChildren->delete();
                }

            }
            foreach ($new["menu_children"] as $valueRequest) {
                $menuChildren = null;
                foreach ($oldChildrens as $oldVal) {
                    if ($oldVal->menu_children_id == $valueRequest['menu_children_id']) {
                        $menuChildren = $oldVal;
                        break;
                    }
                }
            
        $this->addMenuChildren($valueRequest, $menuChildren ,$menu->menu_id);

            }

        }

    }

}
