<?php

namespace Tests\Feature;

use App\Models\Congress;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetTags()
    {
        $congress = factory(Congress::class)->create();
        $tag = factory(Tag::class)->create(['congress_id' =>$congress->congress_id]);
        $response = $this->get('api/congress/' . $congress->congress_id .'/tags')
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testAddTag()
    {
        $congress = factory(Congress::class)->create();
        $tag = $this->getTag();
        $response = $this->post('api/congress/' . $congress->congress_id .'/tags/add', $tag)
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertEquals($dataResponse[0]['label'], $tag['label']);
        $this->assertEquals($dataResponse[0]['congress_id'], $congress->congress_id);
    }

    private function getTag()
    {
        return [
            'label' => $this->faker->word
        ];
    }

}
