<?php

namespace Tests\Feature;

use App\Models\Congress;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\ResourceStand;
use App\Models\Stand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserCongress;
use App\Models\Payment;
use Tymon\JWTAuth\Facades\JWTAuth;

class StandTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /* TODO Verify*/
    public function testGetDocsByCongress()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $resource = factory(Resource::class)->create();
        $resource_stand = factory(ResourceStand::class)->create(['stand_id' => $stand->stand_id, 'resource_id' => $resource->resource_id]);
        $this->get('api/congress/' . $congress->congress_id . '/stand/docs?name=' . $stand->name)
            ->assertStatus(200);
    }

    public function testAddStand()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $resource = factory(Resource::class)->create();
        $resource2 = factory(Resource::class)->create();
        $stand = $this->getFakeStand($congress->congress_id, $organization->organization_id, $resource->resource_id, $resource2->resource_id);
        $this->post('api/congress/' . $congress->congress_id . '/stand/add', $stand)
            ->assertStatus(200);
    }

    public function testEditStand()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $resource = factory(Resource::class)->create();
        $resource_stand = factory(ResourceStand::class)->create(['stand_id' => $stand->stand_id, 'resource_id' => $resource->resource_id]);
        $resource2 = factory(Resource::class)->create();
        $stand->docs = $this->getFakeDocs($resource2->resource_id, $resource_stand->file_name);
        $response = $this->post('api/congress/' . $congress->congress_id . '/stand/add', $stand->toArray())
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $savedStand = Stand::where('stand_id', '=', $dataResponse['stand_id'])
            ->with(['docs', 'organization'])->first();

        $this->assertEquals($stand->stand_id, $dataResponse['stand_id']);
        $this->assertEquals($savedStand->docs[0]->resource_id, $resource2->resource_id);
        $this->assertEquals($savedStand->docs[0]->pivot->file_name, $dataResponse['docs'][0]['pivot']['file_name']);
    }

    public function testModiyStatusStand()
    {
        $congress = factory(Congress::class)->create();
        $organization1 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand1 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization1->organization_id, 'status' => 1]);
        $organization2 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand2 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization2->organization_id, 'status' => 1]);
        $response = $this->put('api/congress/' . $congress->congress_id . '/stand/change-status?all=false&status=0&standId=' . $stand1->stand_id)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $savedStand1 = Stand::where('stand_id', '=', $dataResponse[0]['stand_id'])
            ->first();

        $savedStand2 = Stand::where('stand_id', '=', $dataResponse[1]['stand_id'])
            ->first();
        // verify that only stand1's status was midified to 0
        $this->assertEquals($savedStand1->status, 0);
        $this->assertEquals($savedStand2->status, 1);
    }

    public function testModiyStatusStandAll()
    {
        $congress = factory(Congress::class)->create();
        $organization1 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand1 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization1->organization_id, 'status' => 1]);
        $organization2 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand2 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization2->organization_id, 'status' => 1]);
        $response = $this->put('api/congress/' . $congress->congress_id . '/stand/change-status?all=true&status=0&standId=null')
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $savedStand1 = Stand::where('stand_id', '=', $dataResponse[0]['stand_id'])
            ->with(['docs', 'organization'])->first();

        $savedStand2 = Stand::where('stand_id', '=', $dataResponse[1]['stand_id'])
            ->with(['docs', 'organization'])->first();
        // verify that both stand's status were midified to 0
        $this->assertEquals($savedStand1->status, 0);
        $this->assertEquals($savedStand2->status, 0);
    }

    public function testGetStandByCongress()
    {
        $congress = factory(Congress::class)->create();
        $organization1 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand1 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization1->organization_id, 'status' => 1]);
        $organization2 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand2 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization2->organization_id, 'status' => 0]);
        $response = $this->get('api/user/congress/' . $congress->congress_id . '/stands')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testGetStands()
    {
        $congress = factory(Congress::class)->create();
        $organization1 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand1 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization1->organization_id, 'status' => 1]);
        $organization2 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand2 = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization2->organization_id, 'status' => 0]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/stand')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(2, $dataResponse);
    }
    

    public function testCheckStandRights()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id, 'status' => 1]);
        $user = factory(User::class)->create();
        $userCongress = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user->user_id, 'privilege_id' => 3, 'isSelected' => 1]);
        $payment = factory(Payment::class)->create(['user_id' => $user->user_id, 'congress_id' => $congress->congress_id, 'isPaid' => 1]);
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/congress/' . $congress->congress_id . '/' . $stand->stand_id .  '/checkStandRights')
            ->assertStatus(200);
    }



    private function getFakeStand($congress_id, $organization_id, $resource_id1, $resource_id2)
    {
        return [
            'name' => $this->faker->word,
            'congress_id' => $congress_id,
            'organization_id' => $organization_id,
            'docs' => [
                [
                    'resource_id' => $resource_id1,
                    'pivot' => [
                        'file_name' => $this->faker->word
                    ]
                ],
                [
                    'resource_id' => $resource_id2,
                    'pivot' => [
                        'file_name' => $this->faker->word
                    ]
                ]
            ]
        ];
    }

    private function getFakeDocs($resource_id, $fileName)
    {
        return [
            'docs' => [
                'resource_id' => $resource_id,
                'pivot' => [
                    'file_name' => $fileName
                ]

            ]
        ];
    }
}
