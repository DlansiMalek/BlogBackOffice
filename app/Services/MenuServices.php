<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuChildren;

class MenuServices
{

    public function getAllMenus($show_after_reload)
    {
        return Menu::with(['menu_children' => function ($query) {
            $query->orderBy('index');

        }])->orderBy('index')->where('show_after_reload','=',$show_after_reload)->get();

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
    public function addMenu($new, $menu = null,$show_after_reload)
    {

        if (!$menu) {$menu = new Menu();
            $menuExist = false;
        } else {
            $menuExist = true;
        }

        $menu->key = $new["key"];
        $menu->url = $new["url"];
        $menu->icon = $new["icon"] ? $new["icon"] : "";
        $menu->index = $new["index"];
        $menu->show_after_reload = $show_after_reload;
        if (!$menuExist) {
            $menu->save();
        } else {
             $menu->update();
            }
            return $menu;
    }
    public function setMenus($newMenu,$show_after_reload)
    {
        $oldMenu = $this->getAllMenus($show_after_reload);
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
           $newMenuadd= $this->addMenu($new, $menu,$show_after_reload);
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
            
        $this->addMenuChildren($valueRequest, $menuChildren , $newMenuadd->menu_id);

            }

        }

    }

}
