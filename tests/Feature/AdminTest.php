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
        $user = factory(User::class)->create();
        $congress = factory(Congress::class)->create();
        $userCongress = factory(UserCongress::class)->create(['user_id' => $user->user_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => 3]); 
            $token = JWTAuth::fromUser($superAdmin);
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('api/all-users/listUsers')->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $data = User::where('user_id', '=', $user->user_id)->first();
        $last_page=$dataResponse['last_page'];
        $responseData=$this->withHeader('Authorization', 'Bearer ' . $token)->get('api/all-users/listUsers?page='.$last_page)->assertStatus(200);
        $length_array=count($responseData['data']);
        $this->assertEquals($responseData['data'][$length_array-1]['user_id'],  $data->user_id);
        $this->assertEquals($responseData['data'][$length_array-1]['first_name'],  $data->first_name);
        $this->assertEquals($responseData['data'][$length_array-1]['last_name'],  $data->last_name);
        $this->assertEquals($responseData['data'][$length_array-1]['mobile'],  $data->mobile);
        $this->assertEquals($responseData['data'][$length_array-1]['email'],  $data->email);
        $this->assertEquals($responseData['data'][$length_array-1]['passwordDecrypt'],  $data->passwordDecrypt);     


    }
    
}
