<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Access;

class AccessTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testGetAccessById ()
    {
        $access = factory(Access::class)->create();
        $this->get('api/access/get/' . $access->access_id)
            ->assertStatus(200);
    }

    public function testGetByCongressId()
    {
        $access = factory(Access::class)->create();
        $this->get('api/access/congress/' .$access->congress_id)
            ->assertStatus(200);
    }

    public function testDeleteAccess()
    {
        $access = factory(Access::class)->create();
        $this->delete('api/access/' .$access->access_id)
            ->assertStatus(200);
    }

   /* public function testEditAccess ()
    {
        $data = $this->getFakeDataAccess();

        $oldAccess = factory(Access::class)->create();

    }*/

    private function getFakeDataAccess()
    {
        return [
            'name' => $this->faker->sentence,
            'price' => $this->faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL),
            'duration' => $this->faker->numberBetween($min = 1, $max = 100),
            'max_places' => $this->faker->numberBetween($min = 1, $max = 100),
            'room' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'show_in_program' => $this->faker->numberBetween($min = 1, $max = 3),
            'show_in_register' => $this->faker->numberBetween($min = 1, $max = 3),
            'congress_id' => $this->faker->numberBetween($min = 1, $max = 3),
            'topic_id' => $this->faker->numberBetween($min = 1, $max = 2),
            'access_type_id' => $this->faker->numberBetween($min = 1, $max = 3),
        ];
    }


}
