<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Access;
use App\Models\AccessGame;
use App\Models\Congress;
use App\Models\User;

class AccessTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testGetAccessById()
    {
        $access = factory(Access::class)->create();
        $this->get('api/access/get/' . $access->access_id)
            ->assertStatus(200);
    }

    public function testGetByCongressId()
    {
        $access = factory(Access::class)->create();
        $this->get('api/access/congress/' . $access->congress_id)
            ->assertStatus(200);
    }

    public function testDeleteAccess()
    {
        $access = factory(Access::class)->create();
        $this->delete('api/access/' . $access->access_id)
            ->assertStatus(200);
    }

    // TODO à corriger
    /*public function testGetScoresByCongressIdWithAccess()
    {
        $congress = factory(Congress::class)->create();
        $access = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $access_game = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 10]);
        $access_game2 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 50]);
        $response = $this->get('api/access/congress/' . $congress->congress_id . '/scores?access_id=' . $access->access_id)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        // verify that we get only the biggest score (50)
        $this->assertCount(1, $dataResponse);
        $this->assertEquals($dataResponse[0]['score'], 50);
    }*/

    // TODO à corriger
    /*public function testGetScoresByCongressId()
    {
        $congress = factory(Congress::class)->create();
        $access1 = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $access2 = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $access1_game1 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access1->access_id, 'score' => 10]);
        $access1_game2 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access1->access_id, 'score' => 50]);
        $access2_game1 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access2->access_id, 'score' => 20]);
        $access2_game2 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access2->access_id, 'score' => 100]);

        $response = $this->get('api/access/congress/' . $congress->congress_id . '/scores?access_id=' . null)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        // verify that we get the sum of the biggest scores in each access (150 => 50 from access1 and 100 from access2)
        $this->assertCount(1, $dataResponse);
        $this->assertEquals($dataResponse[0]['score'], 150);
    }*/

    public function testSaveScoreGame()
    {
        $congress = factory(Congress::class)->create();
        $user = factory(User::class)->create();
        $access = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $data = $this->getAccessGame($user->user_id);
        $response = $this->post('api/peaksource/' . $congress->congress_id . '/save-score-game?name=' . $access->name, $data)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $access_game = AccessGame::where('access_game_id', '=', $dataResponse['access_game_id'])
            ->first();
        $this->assertEquals($data['score'], $access_game->score);
        $this->assertEquals($data['user_id'], $access_game->user_id);
        $this->assertEquals($access->access_id, $access_game->access_id);
    }

    // TODO à corriger
    /*public function testGetScoresByCongressPeaksourceByAccessName()
    {
        $congress = factory(Congress::class)->create();
        $access = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $access_game = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 10]);
        $access_game2 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 100]);
        $response = $this->get('api/access/congress/' . $congress->congress_id . '/scores?name=' . $access->name)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        // verify that we get only the biggest score (100)
        $this->assertCount(1, $dataResponse);
        $this->assertEquals($dataResponse[0]['score'], 100);
    }*/

    /* public function testEditAccess ()
    {
        $data = $this->getFakeDataAccess();

        $oldAccess = factory(Access::class)->create();

    }*/

    private function getAccessGame($user_id)
    {
        return [
            'user_id' => $user_id,
            'score' => $this->faker->numberBetween(10, 100)
        ];
    }

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
