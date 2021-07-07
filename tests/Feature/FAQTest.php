<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Congress;
use App\Models\Organization;
use App\Models\Stand;
use App\Models\FAQ;
use Illuminate\Support\Facades\Log;

class FAQTest extends TestCase
{
    public function testGetStandFaqs()
    {
        $congress = factory(Congress::class)->create();
        $organization1 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization1->organization_id, 'status' => 1]);
        $faq = factory(FAQ::class)->create(['stand_id' => $stand->stand_id]);
        $response = $this->get('api/congress/' . $congress->congress_id . '/stand/' . $stand->stand_id . '/FAQ')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $dataResponse);
    }

    public function testAddFAQStand()
    {
        $congress = factory(Congress::class)->create();
        $organization1 = factory(Organization::class)->create(['admin_id' => $this->admin->admin_id]);
        $stand = factory(Stand::class)->create(['congress_id' => $congress->congress_id, 'organization_id' => $organization1->organization_id, 'status' => 1]);
        $faq = factory(FAQ::class)->create(['stand_id' => $stand->stand_id]);
        $faqs[0]= $faq;
        $response =  $this->put('api/congress/' . $congress->congress_id . '/stand/' . $stand->stand_id . '/FAQ', $faqs)
            ->assertStatus(200);
    }


    private function getFakeFAQ($stand_id)
    {
        return [
            'question' => $this->faker->word,
            'response' => $this->faker->word,
            'stand_id' => $stand_id
        ];
    }
}
