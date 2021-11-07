<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Congress;
use App\Models\STag;
use Illuminate\Support\Facades\Log;

class StagTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testgetSTags()
    {
        $congress = factory(Congress::class)->create();
        $stag = factory(STag::class)->create(['congress_id' => $congress->congress_id]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/stags/stand-tag-list')
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testAddTag()
    {
        $congress = factory(Congress::class)->create();
        $stag = $this->getFakeDataSTag($congress->congress_id);
        $response = $this->post('api/congress/' . $congress->congress_id . '/stags/add', $stag)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $sTag = STag::where('stag_id', '=', $dataResponse[0]['stag_id'])
        ->with(['gtag'])
        ->first();
        $this->assertEquals($stag['label'], $sTag->label);
        $this->assertEquals($stag['gstag_id'], $sTag->gstag_id);
        $this->assertEquals($stag['congress_id'], $congress->congress_id);
    }

    private function getFakeDataSTag($congress_id)
    {
        return [
            'congress_id' => $congress_id,
            'label' => $this->faker->word,
            'gstag_id' => $this->faker->numberBetween(1, 20)
        ];
    }

   
}
