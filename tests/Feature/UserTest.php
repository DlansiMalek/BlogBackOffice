<?php

namespace Tests\Feature;

use App\Models\AdminCongress;
use App\Models\Congress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetUsersByCongressPagination ()
    {
        //  api/user/congress/congress_id/list-pagination
        $congress = factory(Congress::class)->create();
        $adminCongress = factory(AdminCongress::class)->create(['admin_id'=> $this->admin->admin_id,
            'congress_id'=> $congress->congress_id, 'privilege_id' => $this->admin->privilege_id]);

        $this->get('api/user/congress/'. $congress->congress_id.'/list-pagination')
            ->assertStatus(200);

    }

    public function testGetUsersByCongressPaginationBadRequest ()
    {
        ///api/user/congress/congress_id/list-pagination
        $congress = factory(Congress::class)->create();

        $this->get('api/user/congress/'. $congress->congress_id.'/list-pagination')
            ->assertStatus(404);
    }

    public function testGetPresencesByCongress()
    {
        //  api/user/congress/1/presence/list
        $congress = factory(Congress::class)->create();

        $this->get('api/user/congress/'. $congress->congress_id.'/presence/list')
            ->assertStatus(200);
    }

    private function getFakeDataUser ($user)
    {
        return [
            $user,
            'privilege_id' => 3,
            'price' => 200,
            'packIds' => [1],
            'accessesId' => [1],
        ];
    }



}
