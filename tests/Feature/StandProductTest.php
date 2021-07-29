<?php

namespace Tests\Feature;

use App\Models\StandProduct;
use App\Models\Congress;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\Stand;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StandProductTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetStandproducts()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $standProduct = factory(StandProduct::class)->create(['stand_id' => $stand->stand_id]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/stand/' . $stand->stand_id . '/products')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testAddStandProduct()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $tag = factory(Tag::class)->create(['congress_id' => $congress->congress_id]);
        $img = factory(Resource::class)->create();
        $file = factory(Resource::class)->create();
        $video = factory(Resource::class)->create();
        $standProduct = $this->renderStandProduct($stand->stand_id, $tag->tag_id, $img, $file, $video);
        $response = $this->put('api/congress/' . $congress->congress_id . '/stand/edit-product/' . $stand->stand_id . '/null', $standProduct)
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $stand_product = StandProduct::where('stand_product_id', '=', $dataResponse['stand_product_id'])
            ->first();
        $this->assertEquals($standProduct['name'], $stand_product->name);
        $this->assertEquals($standProduct['description'], $stand_product->description);
        $this->assertEquals($standProduct['main_img'], $stand_product->main_img);
        $this->assertEquals($standProduct['stand_id'], $stand_product->stand_id);
    }

    public function testEditStandProduct()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $oldStandProduct = factory(StandProduct::class)->create(['stand_id' => $stand->stand_id]);
        $tag = factory(Tag::class)->create(['congress_id' => $congress->congress_id]);
        $img = factory(Resource::class)->create();
        $file = factory(Resource::class)->create();
        $video = factory(Resource::class)->create();
        $standProduct = $this->renderStandProduct($stand->stand_id, $tag->tag_id, $img, $file, $video);
        $response = $this->put('api/congress/' . $congress->congress_id . '/stand/edit-product/' . $stand->stand_id . '/' . $oldStandProduct->stand_product_id, $standProduct)
        ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $stand_product = StandProduct::where('stand_product_id', '=', $oldStandProduct->stand_product_id)
            ->first();
        $this->assertEquals($standProduct['name'], $stand_product->name);
        $this->assertEquals($standProduct['description'], $stand_product->description);
        $this->assertEquals($standProduct['main_img'], $stand_product->main_img);
        $this->assertEquals($standProduct['stand_id'], $stand_product->stand_id);
    }

    public function testDeleteStandProduct()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $standProduct = factory(StandProduct::class)->create(['stand_id' => $stand->stand_id]);
        $this->delete('api/congress/' . $congress->congress_id . '/stand/deletestandproduct/' . $standProduct->stand_product_id)
        ->assertStatus(200);        
    }

    public function testGetStandProductById()
    {
        $congress = factory(Congress::class)->create();
        $organization = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization->organization_id]);
        $standProduct = factory(StandProduct::class)->create(['stand_id' => $stand->stand_id]);
        $this->get('api/congress/' . $congress->congress_id . '/product/' . $standProduct->stand_product_id)
        ->assertStatus(200);        
    }

    private function renderStandProduct($stand_id, $tag_id, $img, $file, $video)
    {
        return [
            'description' => $this->faker->sentence,
            'name' => $this->faker->word,
            'main_img' => $this->faker->sentence,
            'stand_id' => $stand_id,
            'tags' => [
                $tag_id
            ],
            'links' => [
                ['link' => $this->faker->url]
            ],
            'imgs' => [
                [
                    'file_name' => $this->faker->word,
                    'path' => $img->path,
                    'resource_id' => $img->resource_id
                ]
            ],
            'files' => [
                [
                    'file_name' => $this->faker->word,
                    'path' => $file->path,
                    'resource_id' => $file->resource_id
                ]
            ],
            'videos' => [
                [
                    'file_name' => $this->faker->word,
                    'path' => $video->path,
                    'resource_id' => $video->resource_id
                ]
            ]

        ];
    }
}
