<?php

namespace Tests\Feature;

use App\Models\Congress;
use App\Models\WhiteList;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WhiteListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testGetWhiteListsByCongress()
    {
        $congress = factory(Congress::class)->create();
        $whiteList = factory(WhiteList::class)->create(['congress_id' => $congress->congress_id]);
        $this->get('api/user/congress/'. $congress->congress_id.'/white-list')
            ->assertStatus(200);
    }

    public function testAddWhiteList()
    {
        $congress = factory(Congress::class)->create();
        $whiteList = $this->createWhiteList();
        $this->post('api/user/congress/' . $congress->congress_id . '/white-list', $whiteList)
            ->assertStatus(200);

    }

    public function testDeleteWhiteList()
    {
        $congress = factory(Congress::class)->create();
        $whiteList = factory(WhiteList::class)->create(['congress_id' => $congress->congress_id]);
        $this->delete('api/user/congress/'. $congress->congress_id.'/white-list/' . $whiteList->white_list_id)
            ->assertStatus(200);
    }

    public function createWhiteList()
    {
        return ['data' =>
            [
                [
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'mobile' => $this->faker->phoneNumber,
                    'email' => $this->faker->email,
                ],
                [
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'mobile' => $this->faker->phoneNumber,
                    'email' => $this->faker->email,
                ],
                [
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'mobile' => $this->faker->phoneNumber,
                    'email' => $this->faker->email,
                ]
            ]
        ];
    }
}
