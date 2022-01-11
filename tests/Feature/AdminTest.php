<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ConfigCongress;
use App\Models\Congress;
use App\Models\Offre;
use App\Models\PaymentAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserCongress;
use Illuminate\Support\Facades\Log;

class AdminTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetAttestationDiversByCongress()
    {
        $congress = factory(Congress::class)->create();
        $this->get('api/admin/me/congress/' . $congress->congress_id .'/attestation-divers')
            ->assertStatus(200);
    }

    // fail
    public function testEditStatus()
    {
        $status = true;
        $congress = factory(Congress::class)->create();
        $configCongres = factory(ConfigCongress::class)->create(['congress_id' => $congress->congress_id]);;
        $this->get('/api/admin/me/congress/' . $congress->congress_id .'/edit-status/' . $status )
            ->assertStatus(200);
    }

    public function testEditClientPayment()
    {
        $admin = factory(Admin::class)->create(['privilege_id'=> config('privilege.Admin')]);
        $offre = factory(Offre::class)->create(['admin_id'=> $admin->admin_id, 'status' => 1]);
        $paymentAdmin = factory(PaymentAdmin::class)->create(['admin_id'=> $admin->admin_id, 'offre_id' => $offre->offre_id, 'isPaid' => -1]);
        $request = ['isPaid' => $this->faker->numberBetween(0,1)];
        $superAdmin = factory(Admin::class)->create(['privilege_id' => config('privilege.Super_Admin')]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->put('api/admin/' .$admin->admin_id .'/offre/' .$offre->offre_id, $request)
        ->assertStatus(200);
    }
    public function testGetUsersInformation()
    {
        //  api/super-admin/listUsers
        $superAdmin = factory(Admin::class)->create(['privilege_id' => config('privilege.Super_Admin')]);
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create();
        $congress = factory(Congress::class)->create();
        $userCongress = factory(UserCongress::class)->create(['user_id' => $user2->user_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => 3]);
        $userCongress = factory(UserCongress::class)->create(['user_id' => $user3->user_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => 3]); 
            $token = JWTAuth::fromUser($superAdmin);
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('api/super-admin/listUsers')->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
       $userVerification= User::where('user_id', '=', $user2->user_id)->first() ;
       $this->assertEquals($dataResponse['data'][1]['first_name'], $userVerification->first_name);
       $this->assertEquals($dataResponse['data'][1]['last_name'], $userVerification->last_name);
       $this->assertEquals($dataResponse['data'][1]['email'], $userVerification->email);
       $this->assertEquals($dataResponse['data'][1]['mobile'], $userVerification->mobile);
       $this->assertEquals($dataResponse['data'][1]['country_id'], $userVerification->country_id);
      
       $this->assertCount(2, $dataResponse['data']);      
      
    }
}
