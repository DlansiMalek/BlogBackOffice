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
        $admin = factory(Admin::class)->create(['privilege_id'=> 1]);
        $offre = factory(Offre::class)->create(['admin_id'=> $admin->admin_id, 'status' => 1]);
        $paymentAdmin = factory(PaymentAdmin::class)->create(['admin_id'=> $admin->admin_id, 'offre_id' => $offre->offre_id, 'isPaid' => -1]);
        $request = ['isPaid' => $this->faker->numberBetween(0,1)];
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->put('api/admin/' .$admin->admin_id .'/offre/' .$offre->offre_id, $request)
        ->assertStatus(200);
    }

}
