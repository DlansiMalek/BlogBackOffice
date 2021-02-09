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

class StandTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
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
        $response = $this->put('api/congress/' . $congress->congress_id . '/stand/edit/' . $stand->stand_id, $stand->toArray())
        ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $savedStand = Stand::where('stand_id', '=', $dataResponse['stand_id'])
                    ->with(['docs','organization'])->first();
        
        $this->assertEquals($stand->stand_id, $dataResponse['stand_id']);
        $this->assertEquals($savedStand->docs[0]->resource_id, $resource2->resource_id);
        $this->assertEquals($savedStand->docs[0]->pivot->file_name, $dataResponse['docs'][0]['pivot']['file_name']);
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
