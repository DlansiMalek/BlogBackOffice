<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Menu;
use App\Models\MenuChildren;



class MenuTest extends TestCase
{
    public function testGetMenus()
    {
        $menu = factory(Menu::class)->create();
        $menuChildren = factory(MenuChildren::class)->create(['menu_id' => $menu->menu_id]);
        $response = $this->get('api/menu/all/'.$menu->show_after_reload)
            ->assertStatus(200);
  
    }

    public function testSetMenus()
    {
        $newmenu = $this->getFakeMenu();
        $allMenu=Menu::with(['menu_children'])->where('show_after_reload','=',$newmenu['show_after_reload'])->get();
        $allMenu[count($allMenu)]=$newmenu ;
        $response = $this->post('api/menu/add/'.$newmenu['show_after_reload'], $allMenu->toArray())
            ->assertStatus(200);
  
    }

    private function getFakeMenu()
    {
        return [
            'menu_id' => $this->faker->numberBetween(0, 100),         
            'key' => $this->faker->word,
            'icon' => $this->faker->word,
            'url'=>$this->faker->word,
            'index'=>$this->faker->numberBetween(0, 100),
            'show_after_reload'=>$this->faker->numberBetween(0,1),
            'menu_children' => [
                [
                    'key' =>$this->faker->word,
                    'icon' => $this->faker->word,
                    'url'=>$this->faker->word,
                    'index'=>$this->faker->numberBetween(0, 100),
                ]

            ]
                ];
    }

}