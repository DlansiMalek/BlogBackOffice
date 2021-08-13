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
use App\Models\CongressOrganization;
use App\Models\FormInputValue;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

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

    /* TODO Verify
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
    */

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

    public function testSaveUserRegistration()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $access = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $pack = factory(Pack::class)->create(['congress_id' => $congress->congress_id]);
        $user = $this->getUserData($pack->pack_id, $access->access_id);
        $this->post('api/user/congress/' . $congress->congress_id . '/register', $user)
            ->assertStatus(200);
    }

    public function testSaveUsersFromExcel()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $data = $this->getExcelData();
        $this->post('api/user/congress/' . $congress->congress_id . '/save-excel', $data)
            ->assertStatus(200);
    
    }

   
    public function testSaveUsersFromExcelWithOrganization()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $organization = factory(Organization::class)->create();
        $data = $this->getExcelData($organization->oraganization_id);
        $this->post('api/user/congress/' . $congress->congress_id . '/save-excel', $data)
            ->assertStatus(200);
    
    }
   

    public function testSaveUsersFromExcelWithAccesses()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $access1 = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $access2 = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $accessesIds = [$access1->access_id, $access2->access_id];
        $data = $this->getExcelData(null, $accessesIds);
        $this->post('api/user/congress/' . $congress->congress_id . '/save-excel', $data)
            ->assertStatus(200);
    
    }

 
    public function testSaveUsersFromExcelWithOrganizationAndAccesses()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        $organization = factory(Organization::class)->create();
        $access1 = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $access2 = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $accessesIds = [$access1->access_id, $access2->access_id];
        $data = $this->getExcelData($organization->oraganization_id, $accessesIds);
        $this->post('api/user/congress/' . $congress->congress_id . '/save-excel', $data)
            ->assertStatus(200);
    
    }
 

    public function testSaveUsersFromExcelWithFormInputs()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        // create input
        $input = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'form_input_type_id' => 1]);
        // create checkList
        $checkList = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'form_input_type_id' => 6]);
        // create checkList responses
        $value1 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        $value2 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        $value3 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        $value4 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        // create select
        $select = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'form_input_type_id' => 7]);
        // create select responses
        $valueSelect1 = factory(FormInputValue::class)->create(['form_input_id' => $select->form_input_id]);
        $valueSelect2 = factory(FormInputValue::class)->create(['form_input_id' => $select->form_input_id]);
        $valueSelect3 = factory(FormInputValue::class)->create(['form_input_id' => $select->form_input_id]);
        
        $organization = factory(Organization::class)->create();
        $access1 = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $access2 = factory(Access::class)->create(['congress_id' => $congress->congress_id]);
        $accessesIds = [$access1->access_id, $access2->access_id];
        $data = $this->getExcelData($organization->oraganization_id, $accessesIds);
        // add first user responses
        $data['data'][0][$input->key] = $this->faker->word;
        $data['data'][0][$checkList->key] = $value1->value . ';' .$value2->value;
        $data['data'][0][$select->key] = $valueSelect1->value;
        
        // add second user responses
        $data['data'][1][$input->key] = $this->faker->word;
        $data['data'][1][$checkList->key] = $value3->value . ';' .$value4->value;
        $data['data'][1][$select->key] = $valueSelect3->value;
        
        $this->post('api/user/congress/' . $congress->congress_id . '/save-excel', $data)
            ->assertStatus(200);
    
    }
  

    public function testSaveUsersFromExcelWithFormInputsAndOrganizationAndAccesses()
    {
        $congress = factory(Congress::class)->create();
        $congressConfig = factory(ConfigCongress::class)
            ->create(['congress_id' => $congress->congress_id]);
        // create input
        $input = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'form_input_type_id' => 1]);
        // create checkList
        $checkList = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'form_input_type_id' => 6]);
        // create checkList responses
        $value1 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        $value2 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        $value3 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        $value4 = factory(FormInputValue::class)->create(['form_input_id' => $checkList->form_input_id]);
        // create select
        $select = factory(FormInput::class)->create(['congress_id' => $congress->congress_id, 'form_input_type_id' => 7]);
        // create select responses
        $valueSelect1 = factory(FormInputValue::class)->create(['form_input_id' => $select->form_input_id]);
        $valueSelect2 = factory(FormInputValue::class)->create(['form_input_id' => $select->form_input_id]);
        $valueSelect3 = factory(FormInputValue::class)->create(['form_input_id' => $select->form_input_id]);
        
        $data = $this->getExcelData();
        // add first user responses
        $data['data'][0][$input->key] = $this->faker->word;
        $data['data'][0][$checkList->key] = $value1->value . ';' .$value2->value;
        $data['data'][0][$select->key] = $valueSelect1->value;
        
        // add second user responses
        $data['data'][1][$input->key] = $this->faker->word;
        $data['data'][1][$checkList->key] = $value3->value . ';' .$value4->value;
        $data['data'][1][$select->key] = $valueSelect3->value;
        
        $this->post('api/user/congress/' . $congress->congress_id . '/save-excel', $data)
            ->assertStatus(200);
    
    }

    private function getUserData($pack_id, $access_id)
    {
        return [
            'email' => $this->faker->email,
            'privilege_id' => 3,
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->name,
            'packIds' => [
                $pack_id
            ],
            'accessIds' => [
                $access_id
            ]
        ];
    }

    private function getExcelData($organizationId = null, $accessesIds = [])
    {
        return [
            "privilegeId" => 3,
            "organisationId" => $organizationId,
            "data" => [
                [
                    "email" => $this->faker->email,
                    "accessIdTable" => $accessesIds,
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName
                ],
                [
                    "email" => $this->faker->email,
                    "accessIdTable" => $accessesIds,
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName
                ]                    
                
            ]
        ];
            
        
    }

    // TODO Verify
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

    // TODO Verify
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
