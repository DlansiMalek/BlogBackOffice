<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Congress;
use App\Models\Offre;
use App\Models\PaymentAdmin;
use App\Models\PrivilegeMenuChildren;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OffreTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAddOffreFail()
    {
        $offre = [
            'name' => $this->faker->word,
            'value' => $this->faker->numberBetween(500,1000),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ];
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/offre/add', $offre)
            ->assertStatus(400);
    }

    public function testAddOffre()
    {
        $admin = factory(Admin::class)->create(['privilege_id' => 1]);
        $offre = $this->createOffre($admin->admin_id);
        $offre['menus'] = $this->getFakeMenus();
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/offre/add', $offre)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);

        $newOffre = Offre::where('offre_id', '=', $dataResponse['offre']['offre_id'])->first();
        $this->assertEquals($offre['name'], $newOffre->name);
        $this->assertEquals($offre['start_date'], $newOffre->start_date);
        $this->assertEquals($offre['end_date'], $newOffre->end_date);
        $this->assertEquals($offre['offre_type_id'], $newOffre->offre_type_id);
        $this->assertEquals($offre['admin_id'], $newOffre->admin_id);
    }

    public function testEditOffre()
    {
        $admin = factory(Admin::class)->create(['privilege_id' => 1]);
        $offre = $this->createOffre($admin->admin_id);
        $offre['menus'] = $this->getFakeMenus();
        $offreOld = factory(Offre::class)->create(['admin_id' => $admin->admin_id, 'status' => 1]);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->put('api/offre/edit/' . $offreOld->offre_id, $offre)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $newOffre = Offre::where('offre_id', '=', $dataResponse['offre']['offre_id'])->first();
        $this->assertEquals($offreOld->offre_id, $dataResponse['offre']['offre_id']);
        $this->assertEquals($offre['name'], $newOffre->name);
        $this->assertEquals($offre['start_date'], $newOffre->start_date);
        $this->assertEquals($offre['end_date'], $newOffre->end_date);
        $this->assertEquals($offre['offre_type_id'], $newOffre->offre_type_id);
        $this->assertEquals($offre['admin_id'], $newOffre->admin_id);
    }

    public function testGetOffreById()
    {
        $admin = factory(Admin::class)->create(['privilege_id' => 1]);
        $offre = factory(Offre::class)->create(['admin_id'=> $admin->admin_id, 'status' => 1]);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/offre/get/' . $offre->offre_id)
            ->assertStatus(200);
    }

    public function testAddPrivilegeMenuChildren()
    {
        $privilege_id = $this->faker->numberBetween(1,3);
        $congress = factory(Congress::class)->create();
        $menus = $this->getFakeMenus();
        $this->post('api/admin/menus/add/' . $congress->congress_id . '/' . $privilege_id,
            ['menus' => $menus])
            ->assertStatus(200);
    }

    public function testEditPrivilegeMenuChildren()
    {
        $privilege_id = $this->faker->numberBetween(1,3);
        $congress = factory(Congress::class)->create();
        $menus = $this->getFakeMenus();
        $this->post('api/admin/menus/edit/' . $congress->congress_id . '/' . $privilege_id,
            ['menus' => $menus])
            ->assertStatus(200);
    }

    public function createOffre($admin_id){
        return [
            'name' => $this->faker->word,
            'value' => $this->faker->numberBetween(500,1000),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'offre_type_id' =>$this->faker->numberBetween(1,4),
            'admin_id' =>$admin_id,
            'is_mail_pro' => 0
        ];
    }

    public function getFakeMenus() {
        return [
            [
                'menu_id' => 1,
                'menu_children_ids' => [$this->faker->numberBetween(1,5), $this->faker->numberBetween(1,5), $this->faker->numberBetween(1,5)]
            ],
            [
                'menu_id' => 2,
                'menu_children_ids' => [$this->faker->numberBetween(6,8), $this->faker->numberBetween(6,8)]
            ],
            [
                'menu_id' => 3,
                'menu_children_ids' => [$this->faker->numberBetween(9,11), $this->faker->numberBetween(9,11)]
            ],
            [
                'menu_id' => 14,
                'menu_children_ids' => []
            ],
        ];
    }
}
