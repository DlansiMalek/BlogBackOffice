<?php

namespace Tests\Feature;

use App\Models\Congress;
use App\Models\Privilege;
use App\Models\PrivilegeConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PrivilegeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAddPrivilege()
    {
        $congress = factory(Congress::class)->create();
        $privilege = $this->getFakePrivilege($congress->congress_id);
        $response = $this->post('api/privilege/addPrivilege', $privilege)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $newPrivilege = Privilege::where('privilege_id', '=', $dataResponse['response']['privilege_id'])
            ->with('privilegeConfig')->first();
        $this->assertEquals($privilege['name'], $newPrivilege->name);
        $this->assertEquals($privilege['priv_reference'], $newPrivilege->priv_reference);
        $this->assertEquals($privilege['congress_id'], $newPrivilege->privilegeConfig[0]->congress_id);
    }

    public function testHidePrivilege()
    {
        $congress = factory(Congress::class)->create();
        $privilege = factory(Privilege::class)->create();
        $privilegeConfig = factory(PrivilegeConfig::class)->create(['privilege_id' => $privilege->privilege_id,
            'congress_id' => $congress->congress_id, 'status' => 1]);
        $this->get('api/privilege/' .$congress->congress_id .'/hidePrivilege/' .$privilege->privilege_id)
            ->assertStatus(200);
    }

    public function testActivatePrivilege()
    {
        $congress = factory(Congress::class)->create();
        $privilege = factory(Privilege::class)->create();
        $privilegeConfig = factory(PrivilegeConfig::class)->create(['privilege_id' => $privilege->privilege_id,
            'congress_id' => $congress->congress_id, 'status' => 2]);
        $this->get('api/privilege/' .$congress->congress_id .'/activatePrivilege/' .$privilege->privilege_id)
            ->assertStatus(200);
    }

    public function testDeletePrivilege()
    {
        $congress = factory(Congress::class)->create();
        $privilege = factory(Privilege::class)->create();
        $privilegeConfig = factory(PrivilegeConfig::class)->create(['privilege_id' => $privilege->privilege_id,
            'congress_id' => $congress->congress_id]);
        $privilegeDeBaseConfig = factory(PrivilegeConfig::class)->create(['privilege_id' => $privilege->priv_reference,
            'congress_id' => $congress->congress_id]);
        $this->delete('api/privilege/' .$congress->congress_id .'/deletePrivilege/' .$privilege->privilege_id)
            ->assertStatus(200);
    }

    public function testGetPrivilegeById()
    {
        $congress = factory(Congress::class)->create();
        $privilege = factory(Privilege::class)->create();
        $privilegeConfig = factory(PrivilegeConfig::class)->create(['privilege_id' => $privilege->privilege_id,
            'congress_id' => $congress->congress_id]);
        $this->get('api/privilege/getPrivilegeById/' .$privilege->privilege_id)
            ->assertStatus(200);

    }

    public function getFakePrivilege($congress_id=null)
    {
        return [
            'name' => $this->faker->word,
            'priv_reference' => $this->faker->numberBetween(1,3),
            'congress_id' => $congress_id
        ];
    }
}
