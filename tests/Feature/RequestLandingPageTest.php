<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Congress;
use App\Models\RequestLandingPage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RequestLandingPageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testgetLandingPagewithCongressId()
    {
        $congress = factory(Congress::class)->create();
        $admin = factory(Admin::class)->create();
        $RequestLandingPage = factory(RequestLandingPage::class)->create(['congress_id' => $congress->congress_id, 'admin_id' => $admin->admin_id]);
        $this->get('api/request-landing-page/' . $RequestLandingPage->congress_id)
            ->assertStatus(200);
    }
    public function testAddRequestLandingPage()
    {
        $congress = factory(Congress::class)->create();
        $admin = factory(Admin::class)->create();
        $data = $this->getFakerRequestLandingPage($congress->congress_id, $admin->admin_id);
        $response = $this->post('api/request-landing-page/' . $congress->congress_id . '/add', $data)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $newRequestLanding = RequestLandingPage::where('request_landing_page_id', '=', $dataResponse['request_landing_page_id'])->first();
        dd($dataResponse['admin_id'],$newRequestLanding->admin_id,$data['admin_id']);

        $this->assertEquals($data['dns'], $newRequestLanding->dns);
        $this->assertEquals($data['status'], $newRequestLanding->status);
        $this->assertEquals($data['congress_id'], $newRequestLanding->congress_id);
        $this->assertEquals($data['admin_id'], $newRequestLanding->admin_id);
       
    }
    public function testGetRequestLandingPageById()
    {
        $congress = factory(Congress::class)->create();
        $admin = factory(Admin::class)->create();
        $RequestLandingPage = factory(RequestLandingPage::class)->create(['congress_id' => $congress->congress_id, 'admin_id' => $admin->admin_id]);
        $this->get('api/request-landing-page/LandingPage/' . $RequestLandingPage->request_landing_page_id)
            ->assertStatus(200);
    }
    public function testGetAllRequestLandingPage()
    {
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $this->get('api/request-landing-page/list' )
        ->assertStatus(200);

    }
    // public function testEditSatutsRequestLandingPage()
    // {
    //     $congress = factory(Congress::class)->create();
    //     $admin = factory(Admin::class)->create();
    //     $RequestLandingPage = factory(RequestLandingPage::class)->create(['congress_id' => $congress->congress_id, 'admin_id' => $admin->admin_id]);
    //     $response = $this->put('api/request-landing-page/' . $RequestLandingPage->request_landing_page_id)
    //     ->assertStatus(200);
     
    // }
    public function getFakerRequestLandingPage($congress_id, $admin_id)
    {
        return [
            'dns' => $this->faker->word,
            'status' => 0,
            'congress_id' => $congress_id,
            'admin_id' => $admin_id,

        ];
    }
}
