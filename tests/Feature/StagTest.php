<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Congress;
use App\Models\STag;

class StagTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetSTags()
    {
        $congress = factory(Congress::class)->create();
        $stag = factory(STag::class)->create(['congress_id' =>$congress->congress_id]);
        $response = $this->get('api/congress/' . $congress->congress_id .'/stags/stand-tag-list')
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testAddTag()
    {
        $congress = factory(Congress::class)->create();
        $stag = $this->getStag();
        $response = $this->post('api/congress/' . $congress->congress_id .'/stags/add', $stag)
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $stagResp = STag::where('stag_id', '=', $dataResponse['0']['stag_id'])->first();
        $this->assertEquals($stagResp->label, $stag['label']);
        $this->assertEquals($stagResp->congress_id, $congress->congress_id);
    }

    private function getStag()
    {
        return [
            'label' => $this->faker->word
        ];
    }
}
