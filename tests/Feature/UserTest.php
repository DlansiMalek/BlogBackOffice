<?php

namespace Tests\Feature;

use App\Models\Access;
use App\Models\AdminCongress;
use App\Models\ConfigCongress;
use App\Models\ConfigSelection;
use App\Models\Congress;
use App\Models\FormInput;
use App\Models\FormInputResponse;
use App\Models\FormInputType;
use App\Models\Mail;
use App\Models\Pack;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\Admin;
use App\Models\UserCongress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetUsersByCongressPagination()
    {
        //  api/user/congress/congress_id/list-pagination
        $congress = factory(Congress::class)->create();
        $adminCongress = factory(AdminCongress::class)->create(['admin_id' => $this->admin->admin_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => $this->admin->privilege_id]);

        $this->get('api/user/congress/' . $congress->congress_id . '/list-pagination')
            ->assertStatus(200);

    }

    public function testGetUsersByCongressPaginationBadRequest()
    {
        // /api/user/congress/congress_id/list-pagination
        $congress = factory(Congress::class)->create();

        $this->get('api/user/congress/' . $congress->congress_id . '/list-pagination')
            ->assertStatus(404);
    }

    public function testGetPresencesByCongress()
    {
        //  api/user/congress/1/presence/list
        $congress = factory(Congress::class)->create();

        $this->get('api/user/congress/' . $congress->congress_id . '/presence/list')
            ->assertStatus(200);
    }

    public function testSaveUserInscriptionEventPayant()
    {
        // api/user/congress/{{congress_id}}/registerV2
        $congress = factory(Congress::class)
            ->create(['price' => $this->faker->randomFloat(2, 0, 5000), 'congress_type_id' => 1]);
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2')
            ->assertStatus(200);
    }

    public function testSaveUserInscriptionEventPayantFail()
    {
        // api/user/congress/{{congress_id}}/registerV2
        $congress = factory(Congress::class)
            ->create(['price' => $this->faker->randomFloat(2, 0, 5000), 'congress_type_id' => 1]);
        $user = factory(User::class)->create();
        $this->post('api/user/congress/' . $congress->congress_id . '/registerV2')
            ->assertStatus(404);
    }

    public function testSaveUserInscriptionEventGratuitSansSelection()
    {
        $congress = factory(Congress::class)
            ->create(['price' => 0, 'congress_type_id' => 3]);
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2')
            ->assertStatus(200);
    }

    public function testSaveUserInscriptionEventGratuitAvecSelection()
    {
        $congress = factory(Congress::class)
            ->create(['price' => 0, 'congress_type_id' => 2]);
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $configSelection = factory(ConfigSelection::class)
            ->create(['congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2')
            ->assertStatus(200);
    }

    public function testSaveUserInscriptionEventWithPack()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $pack = factory(Pack::class)->create(['congress_id' => $congress->congress_id]);
        $user = factory(User::class)->create();
        $mail = factory(Mail::class)->create(['mail_type_id' => 1, 'congress_id' => $congress->congress_id ]);
        $evaluator = factory(Admin::class)->create();
        $adminCongress = factory(AdminCongress::class)->create(['congress_id' => $congress->congress_id, 'admin_id' => $evaluator->admin_id, 'privilege_id' => 13]);
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2',
                ['packIds' => [$pack->pack_id]])
            ->assertStatus(200);
    }

    public function testSaveUserInscriptionEventAccessObligated()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id, 'nb_ob_access' => 1]);
        $access = factory(Access::class)
            ->create(['congress_id' => $congress->congress_id, 'show_in_register' => 0]);
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2',
                ['accessesId' => [$access->access_id]])
            ->assertStatus(200);
    }

    public function testSaveUserInscriptionEventAccessVersionTwo()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $access = factory(Access::class)
            ->create(['congress_id' => $congress->congress_id, 'show_in_register' => 0]);
        $access2 = factory(Access::class)
            ->create(['congress_id' => $congress->congress_id, 'show_in_register' => 1]);
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2',
                ['accessesId' => [$access2->access_id]])
            ->assertStatus(200);
    }
    public function testGetUsersByCongressPaginationSansDoublon()
    {
        $congress1 = factory(Congress::class)->create();
        $adminCongress1 = factory(AdminCongress::class)->create(['admin_id' => $this->admin->admin_id,
            'congress_id' => $congress1->congress_id, 'privilege_id' => $this->admin->privilege_id]);
        $congress2 = factory(Congress::class)->create();
        $adminCongress2 = factory(AdminCongress::class)->create(['admin_id' => $this->admin->admin_id,
            'congress_id' => $congress2->congress_id, 'privilege_id' => $this->admin->privilege_id]);
        $user = factory(User::class)->create();
        $userCongress1 = factory(UserCongress::class)->create(['congress_id' => $congress1->congress_id, 'user_id' => $user->user_id, 'privilege_id' => 3]);
        $userCongress2 = factory(UserCongress::class)->create(['congress_id' => $congress2->congress_id, 'user_id' => $user->user_id, 'privilege_id' => 3]);
        
        $response = $this->get('api/user/congress/' . $congress1->congress_id . '/list-pagination')
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1 ,$dataResponse['data']);
    }

    public function testGetUsersByCongressPaginationWithSearchPayment()
    {
        // 1 user has payed and the other one didn't
        $search = "payé";
        $congress = factory(Congress::class)->create();
        $adminCongress = factory(AdminCongress::class)->create(['admin_id' => $this->admin->admin_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => $this->admin->privilege_id]);
        
        $user1 = factory(User::class)->create();
        $userCongress1 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user1->user_id, 'privilege_id' => 3]);
        $payment1 = factory(Payment::class)->create(['user_id' => $user1->user_id, 'congress_id' => $congress->congress_id, 'isPaid' => 1]);
        
        $user2 = factory(User::class)->create();
        $userCongress2 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user2->user_id, 'privilege_id' => 3]);
        $payment2 = factory(Payment::class)->create(['user_id' => $user2->user_id, 'congress_id' => $congress->congress_id, 'isPaid' => 0]);

        $response = $this->get('api/user/congress/' . $congress->congress_id . '/list-pagination?search=' . $search)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1 ,$dataResponse['data']);
    }
    
    public function testGetUsersByCongressPaginationWithSearchStatus()
    {
        // 2 users are accepted
        $search = "accepted";
        $congress = factory(Congress::class)->create();
        $adminCongress = factory(AdminCongress::class)->create(['admin_id' => $this->admin->admin_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => $this->admin->privilege_id]);
        
        $user1 = factory(User::class)->create();
        $userCongress1 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user1->user_id, 'privilege_id' => 3, 'isSelected' => 1]);
        
        $user2 = factory(User::class)->create();
        $userCongress2 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user2->user_id, 'privilege_id' => 3, 'isSelected' => 1]);

        $user3 = factory(User::class)->create();
        $userCongress3 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user3->user_id, 'privilege_id' => 3, 'isSelected' => -1]);

        $user4 = factory(User::class)->create();
        $userCongress4 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user4->user_id, 'privilege_id' => 3, 'isSelected' => 0]);

        $response = $this->get('api/user/congress/' . $congress->congress_id . '/list-pagination?search=' . $search)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(2 ,$dataResponse['data']);
    }

    public function testGetUsersByCongressPaginationWithSearchCountry()
    {
        // Only one user is from tunisia
        $search = "Tunisia";
        $congress = factory(Congress::class)->create();
        $adminCongress = factory(AdminCongress::class)->create(['admin_id' => $this->admin->admin_id,
            'congress_id' => $congress->congress_id, 'privilege_id' => $this->admin->privilege_id]);
        
        $user1 = factory(User::class)->create(['country_id' => 'TUN']);
        $userCongress1 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user1->user_id, 'privilege_id' => 3]);
        
        $user2 = factory(User::class)->create(['country_id' => 'BHS']);
        $userCongress2 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user2->user_id, 'privilege_id' => 3]);

        $user3 = factory(User::class)->create(['country_id' => 'TUR']);
        $userCongress3 = factory(UserCongress::class)->create(['congress_id' => $congress->congress_id, 'user_id' => $user3->user_id, 'privilege_id' => 3]);

        $response = $this->get('api/user/congress/' . $congress->congress_id . '/list-pagination?search=' . $search)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);
        $this->assertCount(1 ,$dataResponse['data']);
    }

    // TODO Correction
    /*public function testSaveUserInscriptionEventWithQuestionsNotRequired()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $formInput = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'required' => 0]);
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2')
            ->assertStatus(200);
    }*/

    // TODO Correction
    /*public function testSaveUserInscriptionEventWithQuestionsRequired()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $formInput = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'required' => 0]);
        $formInputType = FormInputType::where('form_input_type_id', '=', $formInput->form_input_type_id)
            ->first();
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/user/congress/' . $congress->congress_id . '/registerV2',
                ['responses' => ['congress_id' => $congress->congress_id,
                    'label' => $this->faker->sentence,
                    'form_input_id' => $formInput->form_input_id,
                    'form_input_type_id' => $formInput->form_input_type_id,
                    'response' => $this->faker->sentence,
                    'required' => $formInput->required,
                    'type' => ['name' => $formInputType->name,
                        'form_input_type_id' => $formInput->form_input_type_id],
                    'values' => []
                ]
                ])->assertStatus(200);
    }*/

}
