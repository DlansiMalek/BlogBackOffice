<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resource;
use App\Models\Congress;
use App\Models\CongressOrganization;
use App\Models\Organization;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

     
    public function testGetCongressOrganizations()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create();
        
        $response = $this->get('api/congress/' . $congress->congress_id . '/organization')
                ->assertStatus(200);
    }
     
     
    public function testAddOrganization()
    {
        $admin = factory(Admin::class)->create();
        $congress = factory(Congress::class)->create();
        $data = $this->getFakeOrganization($congress->congress_id,$admin->admin_id);

        $response = $this->post('api/congress/' . $congress->congress_id . '/organization', $data)
            ->assertStatus(200);
    
        $dataResponse = json_decode($response->getContent(), true);
        $organization = Organization::where('organization_id', '=', $dataResponse['organization_id'])->first();

       $this->assertEquals($data['name'], $organization->name);
        $this->assertEquals($data['description'], $organization->description);
        $this->assertEquals($data['mobile'], $organization->mobile);
        $this->assertEquals($data['is_sponsor'], $organization->is_sponsor);
        $this->assertEquals($data['logo_position'], $organization->logo_position);
    }
/*
    public function testEditOrganization()
    {
        $congress = factory(Congress::class)->create();
        $resource = factory(Resource::class)->create();
        $admin = factory(Admin::class)->create();
        $oldOrganization = factory(Organization::class)->create(['resource_id' => $resource->resource_id]);
        $resource1 = factory(Resource::class)->create();
        $data = $this->getFakeOrganization($resource1->resource_id, $admin->admin_id);
        
        $response = $this->put('api/organization/' .$oldOrganization->organization_id . '/edit', $data)
            ->assertStatus(200);
            
        $dataResponse = json_decode($response->getContent(), true);
        $organization = Organization::where('organization_id', '=', $dataResponse['organization_id'])->first();

        $this->assertEquals($data['name'], $organization->name);
        $this->assertEquals($data['description'], $organization->description);
        $this->assertEquals($data['mobile'], $organization->mobile);
        $this->assertEquals($data['resource_id'], $organization->resource_id);
        $this->assertEquals($data['is_sponsor'], $organization->is_sponsor);
        $this->assertEquals($data['logo_position'], $organization->logo_position);
    }

    public function testDeleteOrganization()
    {
        $congress = factory(Congress::class)->create();
        $resource = factory(Resource::class)->create();
        $organization = factory(Organization::class)->create(['resource_id' => $resource->resource_id]);
        $congress_organization = factory(CongressOrganization::class)->create(['congress_id' => $congress->congress_id,
                'organization_id' => $organization->organization_id]);
        
        $response = $this->delete('api/congress/' . $congress->congress_id . '/delete-organization/' .$organization->organization_id)
        ->assertStatus(200);
    }
*/
    private function getFakeOrganization($congress_id, $admin_id = null)
    {
        return [
            'congress_id' => $congress_id,
            'name' => $this->faker->sentence,
            'email' => $this->faker->email,
            'description' => $this->faker->sentence,
            'mobile' => $this->faker->phoneNumber,
            'is_sponsor' => $this->faker->numberBetween(0,1),
            'logo_position' => 'Left',
            'admin' => [ 'admin_id' => $admin_id]
        ];
    }
     
}
