<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Congress;
use App\Models\GSTag;
use App\Models\STag;

class GSTagTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetGSTags()
    {
        $congress = factory(Congress::class)->create();
        $gstag = factory(GSTag::class)->create(['congress_id' => $congress->congress_id]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/gstags/stand-gtag-list')
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testAddGSTag()
    {
        $congress = factory(Congress::class)->create();
        $gstag = $this->getGStag();
        $response = $this->post('api/congress/' . $congress->congress_id . '/gstags/add', $gstag)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertEquals($dataResponse[0]['label'], $gstag['label']);
        $this->assertEquals($dataResponse[0]['congress_id'], $congress->congress_id);
    }

    private function getGStag()
    {
        return [
            'label' => $this->faker->word
        ];
    }

    public function testGetStagByGTagId()
    {
        $congress = factory(Congress::class)->create();
        $gstag = factory(GSTag::class)->create(['congress_id' => $congress->congress_id]);
        $stag = factory(STag::class)->create(['congress_id' => $congress->congress_id]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/gstags/stand-groupe-tags/' . $gstag->gstag_id)
        ->assertStatus(200);
    }

}
