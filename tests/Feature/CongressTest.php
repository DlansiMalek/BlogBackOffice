<?php

namespace Tests\Feature;

use App\Models\AdminCongress;
use App\Models\ConfigCongress;
use App\Models\ConfigSubmission;
use App\Models\Congress;
use stdClass;
use Tests\TestCase;
use App\Services\Utils;

class CongressTest extends TestCase
{
    /**
     * A basic feature test getting congress by specific Id.
     *
     * @return void
     */
    public function testGetCongressById()
    {
        // url api/congress/{congressId}
        $congress = factory(Congress::class)->create();

        $this->get('api/congress/' . $congress->congress_id)
            ->assertStatus(200);
    }

    /**
     * A basic feature test add congress bad request
     *
     * @return void
     */
    public function testAddCongressBadRequest()
    {
        // Url : api/admin/me/congress/add
        $data = [
            'name' => $this->faker->sentence
        ];

        $this->post('api/admin/me/congress/add', $data)
            ->assertStatus(400);

    }

    /**
     * A basic feature test add congress simple
     *
     * @return void
     */
    public function testAddCongressSimple()
    {
        // Url : api/admin/me/congress/add
        $data = $this->getFakeDataCongress();

        $response = $this->post('api/admin/me/congress/add', $data)
            ->assertStatus(201);

        $dataResponse = json_decode($response->getContent(), true);

        // *** Verify Adding Congress ***
        $congress = Congress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($data['name'], $congress->name);
        $this->assertEquals($data['start_date'], $congress->start_date);
        $this->assertEquals($data['end_date'], $congress->end_date);
        $this->assertEquals($data['congress_type_id'] == '1' ? $data['price'] : 0, $congress->price);
        $this->assertEquals($data['congress_type_id'], $congress->congress_type_id);
        $this->assertEquals($data['description'], $congress->description);

        // *** Verify Adding Config Congress ***
        $configCongress = ConfigCongress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($data['config']['free'], $configCongress->free);
        $this->assertNull($configCongress->logo);
        $this->assertNull($configCongress->banner);
        $this->assertNull($configCongress->feedback_start);
        $this->assertNull($configCongress->program_link);
        $this->assertNull($configCongress->voting_token);
        $this->assertNull($configCongress->nb_ob_access);
        $this->assertEquals(0, $configCongress->auto_presence);
        $this->assertNull($configCongress->link_sondage);
        $this->assertEquals('Workshop', $configCongress->access_system);
        $this->assertEquals(1, $configCongress->status);
        $this->assertEquals($dataResponse['congress_id'], $configCongress->congress_id);

        // *** Verify Adding Admin Congress ***
        $adminCongress = AdminCongress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($this->admin->admin_id, $adminCongress->admin_id);
        $this->assertEquals($dataResponse['congress_id'], $adminCongress->congress_id);
        $this->assertNull($adminCongress->organization_id);
        $this->assertEquals(1, $adminCongress->privilege_id);
    }

    /**
     * A basic feature test delete congress
     *
     * @return void
     */


    /**
     * A basic feature test edit congress
     *
     * @return void
     */
    public function testEditCongress()
    {
        // Url : api/admin/me/congress/edit

        $data = $this->getFakeDataCongress();

        $congressOld = factory(Congress::class)->create();

        $adminCongressOld = factory(AdminCongress::class)->create([
            'admin_id' => $this->admin->admin_id,
            'congress_id' => $congressOld->congress_id,
            'privilege_id' => 1
        ]);

        $configCongressOld = factory(ConfigCongress::class)->create(['congress_id' => $congressOld->congress_id]);

        $response = $this->post('api/admin/me/congress/' . $congressOld->congress_id . '/edit', $data)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        // *** Verify Editing Congress ***
        $congress = Congress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($congressOld->congress_id, $dataResponse['congress_id']);
        $this->assertEquals($data['name'], $congress->name);
        $this->assertEquals($data['start_date'], $congress->start_date);
        $this->assertEquals($data['end_date'], $congress->end_date);
        $this->assertEquals($data['congress_type_id'] == '1' ? $data['price'] : 0, $congress->price);
        $this->assertEquals($data['congress_type_id'], $congress->congress_type_id);
        $this->assertEquals($data['description'], $congress->description);

        // *** Verify Adding Config Congress ***
        $configCongress = ConfigCongress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($data['config']['free'], $configCongress->free);
        $this->assertEquals($configCongressOld->logo, $configCongress->logo);
        $this->assertEquals($configCongressOld->banner, $configCongress->banner);
        $this->assertNull($configCongress->feedback_start);
        $this->assertEquals($configCongressOld->program_link, $configCongress->program_link);
        $this->assertNull($configCongress->voting_token);
        $this->assertNull($configCongress->nb_ob_access);
        $this->assertEquals(0, $configCongress->auto_presence);
        $this->assertNull($configCongress->link_sondage);
        $this->assertEquals('Workshop', $configCongress->access_system);
        $this->assertEquals(1, $configCongress->status);
        $this->assertEquals($configCongressOld->congress_id, $configCongress->congress_id);

        // *** Verify Adding Admin Congress ***
        $adminCongress = AdminCongress::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($this->admin->admin_id, $adminCongress->admin_id);
        $this->assertEquals($adminCongressOld->congress_id, $adminCongress->congress_id);
        $this->assertNull($adminCongress->organization_id);
        $this->assertEquals(1, $adminCongress->privilege_id);
    }

    public function testGetMinimalCongressById()
    {
        $congress = factory(Congress::class)->create();
        $this->get('api/congress/' . $congress->congress_id .'/min')
            ->assertStatus(200);
    }

    public function testGetMailTypeById ()
    {
        $congress = factory(Congress::class)->create();
        $this->get('/api/congress/mail/types/' . $congress->congress_id  )
            ->assertStatus(200);
    }

    public function testGetCongressOrganizations()
    {
        //  api/congress/1/organization
        $congress = factory(Congress::class)->create();
        $this->get('/api/congress/' . $congress->congress_id .'/organization' )
            ->assertStatus(200);
    }

    public function testAddConfigSubmission()
    {
        $congress = factory(Congress::class)->create();
        $config = factory(ConfigCongress::class)->create(['congress_id' => $congress->congress_id, 'is_submission_enabled' => 1]);
        $config['privileges'] = [3];
        $submission = $this->getDataSubmission();
        $request = ['congress' => $config, 'submission' => $submission];
        $this->post('/api/admin/me/congress/' . $congress->congress_id .'/edit-config', $request )
            ->assertStatus(200);

        $configSubmission = ConfigSubmission::where('congress_id', '=', $congress->congress_id)->first();
        $this->assertEquals($configSubmission->max_words, $submission['max_words']);
        $this->assertEquals($configSubmission->num_evaluators, $submission['num_evaluators']);
    }

    public function testDeleteConfigSubmission()
    {
        $congress = factory(Congress::class)->create();
        $config = factory(ConfigCongress::class)->create(['congress_id' => $congress->congress_id, 'is_submission_enabled' => 0]);
        $config['privileges'] = [3];
        $submission = factory(ConfigSubmission::class)->create(['congress_id' => $congress->congress_id]);
        $request = ['congress' => $config, 'submission' => []];
        $this->post('/api/admin/me/congress/' . $congress->congress_id .'/edit-config', $request )
            ->assertStatus(200);

        $configSubmission = ConfigSubmission::where('congress_id', '=', $congress->congress_id)->first();
        // make sure that there isn't any ConfigSubmission
        $this->assertNull($configSubmission);
    }

    private function getFakeDataCongress()
    {
        return [
            'name' => $this->faker->sentence,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'price' => $this->faker->randomFloat(2, 0, 5000),
            'congress_type_id' => strval($this->faker->numberBetween(1, 3)),
            'private' =>$this->faker->numberBetween(0, 1),

            'description' => $this->faker->paragraph,
            'config' => [
                'free' => $this->faker->numberBetween(0, 100),
                'access_system' => 'Workshop',
                'prise_charge_option' => $this->faker->numberBetween(0, 1),
                'is_online'=>$this->faker->numberBetween(0,1),
                'status' => 1,
                'is_submission_enabled' => $this->faker->numberBetween(0, 1),
                'currency_code' => 'TND',
            ],
            'config_selection' => [
                'num_evaluators' => $this->faker->numberBetween(1, 20),
                'selection_type' =>  $this->faker->numberBetween(0, 2),
                'start_date'=> $this->faker->date(),
                'end_date' => $this->faker->date(),
            ],
        ];
    }

    private function getDataSubmission()
    {
        return [
            'end_submission_date' => $this->faker->date(),
            'start_submission_date' => $this->faker->date(),
            'max_words' => $this->faker->numberBetween(100, 500),
            'num_evaluators' => $this->faker->numberBetween(1, 5),
        ];
    }
}
