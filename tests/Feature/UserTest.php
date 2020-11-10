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
use App\Models\Pack;
use App\Models\User;
use App\Models\UserAccess;
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
        ///api/user/congress/congress_id/list-pagination
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
