<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuChildren;
use Tests\TestCase;

class MenuTest extends TestCase
{
    public function testGetMenus()
    {
        $menu = factory(Menu::class)->create();
        $menuChildren = factory(MenuChildren::class)->create(['menu_id' => $menu->menu_id]);
        $response = $this->get('api/menu/all/' . $menu->show_after_reload)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        foreach ($dataResponse as $data) {
            return $menuAdded = menu::where('menu_id', '=', $data['menu_id'])
                ->first();
        }
        $this->assertEquals($menuAdded->key, $menu->key);
        $this->assertEquals($menuAdded->icon, $menu->icon);
        $this->assertEquals($menuAdded->url, $menu->url);
        $this->assertEquals($menuAdded->index, $menu->index);
        $this->assertEquals($menuAdded->show_after_reload, $menu->show_after_reload);
        $this->assertEquals($menuAdded['menu_children']->key, $menu['menu_children']->key);
        $this->assertEquals($menuAdded['menu_children']->url, $menu['menu_children']->url);
        $this->assertEquals($menuAdded['menu_children']->index, $menu['menu_children']->index);

    }

    public function testSetMenus()
    {
        $newmenu = $this->getFakeMenu();
        $allMenu = Menu::with(['menu_children'])->where('show_after_reload', '=', $newmenu['show_after_reload'])->get();
        $allMenu[count($allMenu)] = $newmenu;
        $response = $this->post('api/menu/add/' . $newmenu['show_after_reload'], $allMenu->toArray())->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        foreach ($dataResponse as $data) {
            return $menu = menu::where('menu_id', '=', $data['menu_id'])
                ->first();
        }
        $this->assertEquals($newmenu->key, $menu->key);
        $this->assertEquals($newmenu->icon, $menu->icon);
        $this->assertEquals($newmenu->url, $menu->url);
        $this->assertEquals($newmenu->index, $menu->index);
        $this->assertEquals($newmenu->show_after_reload, $menu->show_after_reload);
        $this->assertEquals($newmenu['menu_children']->key, $menu['menu_children']->key);
        $this->assertEquals($newmenu['menu_children']->url, $menu['menu_children']->url);
        $this->assertEquals($newmenu['menu_children']->index, $menu['menu_children']->index);

    }

    private function getFakeMenu()
    {
        return [
            'menu_id' => $this->faker->numberBetween(0, 100),
            'key' => $this->faker->word,
            'icon' => $this->faker->word,
            'url' => $this->faker->word,
            'index' => $this->faker->numberBetween(0, 100),
            'show_after_reload' => $this->faker->numberBetween(0, 1),
            'menu_children' => [
                [
                    'menu_children_id' => $this->faker->numberBetween(0, 100),
                    'key' => $this->faker->word,
                    'icon' => $this->faker->word,
                    'url' => $this->faker->word,
                    'index' => $this->faker->numberBetween(0, 100),
                ],

            ],
        ];
    }

}
