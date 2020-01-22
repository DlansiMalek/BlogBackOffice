<?php

namespace Tests\Feature;

use App\Models\AdminCongress;
use App\Models\ConfigCongress;
use App\Models\Congress;
use Tests\TestCase;

class CongressTest extends TestCase
{
    /**
     * A basic feature test getting congress by specific Id.
     *
     * @return void
     */
    public function testGetCongressById()
    {
        // url api/congress/{congressId}
        $congress = factory(Congress::class)->create();

        $this->get('api/congress/' . $congress->congress_id)
            ->assertStatus(200);
    }

    /**
     * A basic feature test add congress bad request
     *
     * @return void
     */
    public function testAddCongressBadRequest()
    {
        // Url : api/admin/me/congress/add
        $data = [
            'name' => $this->faker->sentence
        ];

        $this->post('api/admin/me/congress/add', $data)
            ->assertStatus(400);

    }

    /**
     * A basic feature test add congress simple
     *
     * @return void
     */
    public function testAddCongressSimple()
    {
        // Url : api/admin/me/congress/add
        $data = [
            'name' => $this->faker->sentence,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'price' => $this->faker->randomFloat(2, 0, 5000),
            'congress_type_id' => $this->faker->numberBetween(1, 3),
            'description' => $this->faker->paragraph,
            'config' => [
                'has_payment' => $this->faker->numberBetween(0, 1),
                'free' => $this->faker->numberBetween(0, 1),
                'prise_charge_option' => $this->faker->numberBetween(0, 1)
            ]
        ];

        $response = $this->post('api/admin/me/congress/add', $data)
            ->assertStatus(201);

        $dataResponse = json_decode($response->getContent(), true);

        // *** Verify Adding Congress ***
        $congress = Congress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($data['name'], $congress->name);
        $this->assertEquals($data['start_date'], $congress->start_date);
        $this->assertEquals($data['end_date'], $congress->end_date);
        $this->assertEquals($data['congress_type_id'] == 1 ? $data['price'] : 0, $congress->price);
        $this->assertEquals($data['congress_type_id'], $congress->congress_type_id);
        $this->assertEquals($data['description'], $congress->description);

        // *** Verify Adding Config Congress ***
        $configCongress = ConfigCongress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($data['config']['has_payment'], $configCongress->has_payment);
        $this->assertEquals($data['config']['free'], $configCongress->free);
        $this->assertEquals($data['config']['prise_charge_option'], $configCongress->prise_charge_option);
        $this->assertNull($configCongress->logo);
        $this->assertNull($configCongress->banner);
        $this->assertNull($configCongress->feedback_start);
        $this->assertNull($configCongress->program_link);
        $this->assertNull($configCongress->voting_token);
        $this->assertNull($configCongress->nb_ob_access);
        $this->assertEquals(0, $configCongress->auto_presence);
        $this->assertNull($configCongress->link_sondage);
        $this->assertEquals('Ateliers', $configCongress->access_system);
        $this->assertEquals(1, $configCongress->status);
        $this->assertEquals($dataResponse['congress_id'], $configCongress->congress_id);

        // *** Verify Adding Admin Congress ***
        $adminCongress = AdminCongress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($this->admin->admin_id, $adminCongress->admin_id);
        $this->assertEquals($dataResponse['congress_id'], $adminCongress->congress_id);
        $this->assertNull($adminCongress->organization_id);
        $this->assertEquals(1, $adminCongress->privilege_id);
    }
}
