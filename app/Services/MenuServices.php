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
            if (!$menu) {
                $menu = new Menu();
            }

            $menu->menu_id = $new["menu_id"];
            $menu->key = $new["key"];
            $menu->icon = $new["icon"];
            $menu->index = $new["index"];

            $menu->save();

            $oldChildrens = MenuChildren::where('menu_id', '=', $menu->menu_id)->get();
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
                if (!$menuChildren) {
                    $menuChildren = new MenuChildren();
                }

                $menuChildren->menu_children_id = $valueRequest['menu_children_id'];
                $menuChildren->key = $valueRequest["key"];
                $menuChildren->icon = $valueRequest["icon"];
                $menuChildren->url = $valueRequest["url"];
                $menuChildren->menu_id = $menu->menu_id;
                $menuChildren->index = $valueRequest["index"];

                if ($menuChildren->menu_children_id) {
                    $menuChildren->update();
                } else {
                    $menuChildren->save();
                }

            }

        }

    }

}
