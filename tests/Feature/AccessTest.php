<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Access;
use App\Models\AccessGame;
use App\Models\Congress;
use App\Models\User;
use App\Models\UserCongress;

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

    public function testGetScoresByCongressIdWithAccess()
    {
        $congress = factory(Congress::class)->create();
        $access = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $user_congress = factory(UserCongress::class)->create(['user_id' => $user->user_id, 'congress_id' => $congress->congress_id, 'privilege_id' => 3]);
        $access_game = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 10]);
        $access_game2 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 50]);
        $response = $this->get('api/access/congress/' . $congress->congress_id . '/scores?access_id=' . $access->access_id)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        // verify that we get only the biggest score (50)
        $this->assertCount(1, $dataResponse);
        $this->assertEquals($dataResponse[0]['score'], 50);
    }


    public function testGetScoresByCongressId()
    {
        $congress = factory(Congress::class)->create();
        $access1 = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $access2 = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $user_congress = factory(UserCongress::class)->create(['user_id' => $user->user_id, 'congress_id' => $congress->congress_id, 'privilege_id' => 3]);
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
    }

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
    public function testGetScoresByCongressPeaksourceByAccessName()
    {
        $congress = factory(Congress::class)->create();
        $access = factory(Access::class)->create(['access_type_id' => 4, 'congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $user_congress = factory(UserCongress::class)->create(['user_id' => $user->user_id, 'congress_id' => $congress->congress_id, 'privilege_id' => 3]);
        $access_game = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 10]);
        $access_game2 = factory(AccessGame::class)->create(['user_id' => $user->user_id, 'access_id' => $access->access_id, 'score' => 100]);
        $response = $this->get('api/access/congress/' . $congress->congress_id . '/scores?name=' . $access->name)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        // verify that we get only the biggest score (100)
        $this->assertCount(1, $dataResponse);
        $this->assertEquals($dataResponse[0]['score'], 100);
    }

    // TODO à corriger
    /*public function testEditAccessStatus()
    {
        $congress = factory(Congress::class)->create();
        $access1 = factory(Access::class)->create(['congress_id' => $congress->congress_id, 'status' => 1]);
        $access2 = factory(Access::class)->create(['congress_id' => $congress->congress_id, 'status' => 1]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/access/change-status?all=false&status=0&accessId=' . $access1->access_id)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $savedAccess1 = Access::where('access_id', '=', $dataResponse[0]['access_id'])
            ->first();

        $savedAccess2 = Access::where('access_id', '=', $dataResponse[1]['access_id'])
            ->first();
        // verify that only access1's status was midified to 0
        $this->assertEquals($savedAccess1->status, 0);
        $this->assertEquals($savedAccess2->status, 1);
    }*/

    public function testEditAllAccessStatus()
    {
        $congress = factory(Congress::class)->create();
        $access1 = factory(Access::class)->create(['congress_id' => $congress->congress_id, 'status' => 1]);
        $access2 = factory(Access::class)->create(['congress_id' => $congress->congress_id, 'status' => 1]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/access/change-status?all=true&status=0&accessId=null')
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $savedAccess1 = Access::where('access_id', '=', $dataResponse[0]['access_id'])
            ->first();

        $savedAccess2 = Access::where('access_id', '=', $dataResponse[1]['access_id'])
            ->first();
        // verify that both accesses's status was midified to 0
        $this->assertEquals($savedAccess1->status, 0);
        $this->assertEquals($savedAccess2->status, 0);
    }

    public function testUploadExcelAccess()
    {
        $congress = factory(Congress::class)->create();
        $accessTypeId = $this->faker->numberBetween(1, 3);
        $data = $this->renderExcelData($accessTypeId);
        $response = $this->post('api/access/' .$congress->congress_id . '/uploadExcel', $data)
            ->assertStatus(200);
    }

    private function renderExcelData($accessTypeId)
    {
        return [
            "accessTypeId" => $accessTypeId,
            "data" => [
                [
                    "email" => $this->faker->email,
                    "end_date" => $this->faker->date,
                    "line" => 1,
                    "start_date" => $this->faker->date
                ],
                [
                    "email" => $this->faker->email,
                    "end_date" => $this->faker->date,
                    "line" => 2,
                    "start_date" => $this->faker->date
                ],
                [
                    "email" => $this->faker->email,
                    "end_date" => $this->faker->date,
                    "line" => 3,
                    "start_date" => $this->faker->date
                ]
            ]

        ];
    }

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
