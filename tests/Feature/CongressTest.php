<?php

namespace Tests\Feature;

use App\Models\Congress;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CongressTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

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

        $this->post('api/admin/me/congress/add', $data)
            ->assertStatus(200);

    }
}
