<?php

namespace Tests\Feature;

use App\Models\AdminCongress;
use App\Models\ConfigCongress;
use App\Models\ConfigLP;
use App\Models\Congress;
use App\Models\LPSpeaker;
use stdClass;
use Tests\TestCase;
use App\Services\Utils;
use DateTime;

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
        $this->get('api/congress/' . $congress->congress_id . '/min')
            ->assertStatus(200);
    }



    public function testGetMailTypeById()
    {
        $congress = factory(Congress::class)->create();
        $this->get('/api/congress/mail/types/' . $congress->congress_id)
            ->assertStatus(200);
    }

    public function testGetCongressOrganizations()
    {
        //  api/congress/1/organization
        $congress = factory(Congress::class)->create();
        $this->get('/api/congress/' . $congress->congress_id . '/organization')
            ->assertStatus(200);
    }

    public function testGetConfigLandingPage()
    {
        $congress = factory(Congress::class)->create();
        $configLP = factory(ConfigLP::class)->create(['congress_id' => $congress->congress_id]);
        $this->get('/api/admin/me/congress/' . $congress->congress_id . '/landing-page/get-config')
            ->assertStatus(200);
    }

    public function testEditConfigLandingPage()
    {
        $congress = factory(Congress::class)->create();
        $data = $this->getConfigLP();
        $response = $this->post('/api/admin/me/congress/' . $congress->congress_id . '/landing-page/edit-config', $data)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);

        $configLP = ConfigLP::where('congress_id', '=', $dataResponse['config_landing_page']['congress_id'])
            ->first();

        $this->assertEquals($congress->congress_id, $dataResponse['config_landing_page']['congress_id']);
        $this->assertEquals($data['header_logo_event'], $configLP->header_logo_event);
        $this->assertEquals($data['is_inscription'], $configLP->is_inscription);
        $this->assertEquals($data['home_banner_event'], $configLP->home_banner_event);
        $this->assertEquals($data['home_title'], $configLP->home_title);
        $this->assertEquals($data['home_description'], $configLP->home_description);
        $this->assertEquals($data['prp_banner_event'], $configLP->prp_banner_event);
        $this->assertEquals($data['prp_title'], $configLP->prp_title);
        $this->assertEquals($data['prp_description'], $configLP->prp_description);
        $this->assertEquals($data['speaker_title'], $configLP->speaker_title);
        $this->assertEquals($data['speaker_description'], $configLP->speaker_description);
        $this->assertEquals($data['sponsor_title'], $configLP->sponsor_title);
        $this->assertEquals($data['sponsor_description'], $configLP->sponsor_description);
        $this->assertEquals($data['prg_title'], $configLP->prg_title);
        $this->assertEquals($data['prg_description'], $configLP->prg_description);
        $this->assertEquals($data['contact_title'], $configLP->contact_title);
        $this->assertEquals($data['contact_description'], $configLP->contact_description);
        $this->assertEquals($data['event_link_fb'], $configLP->event_link_fb);
        $this->assertEquals($data['event_link_instagram'], $configLP->event_link_instagram);
        $this->assertEquals($data['event_link_linkedin'], $configLP->event_link_linkedin);
        $this->assertEquals($data['event_link_twitter'], $configLP->event_link_twitter);
    }

    public function testAddLandingPageSpeaker()
    {
        $congress = factory(Congress::class)->create();
        $data = $this->getLPSpeaker();
        $response = $this->post('/api/admin/me/congress/' . $congress->congress_id . '/landing-page/add-speaker', $data)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);

        $lPSpeaker = LPSpeaker::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($congress->congress_id, $dataResponse['congress_id']);
        $this->assertEquals($data['first_name'], $lPSpeaker->first_name);
        $this->assertEquals($data['last_name'], $lPSpeaker->last_name);
        $this->assertEquals($data['role'], $lPSpeaker->role);
        $this->assertEquals($data['profile_img'], $lPSpeaker->profile_img);
        $this->assertEquals($data['fb_link'], $lPSpeaker->fb_link);
        $this->assertEquals($data['linkedin_link'], $lPSpeaker->linkedin_link);
        $this->assertEquals($data['instagram_link'], $lPSpeaker->instagram_link);
        $this->assertEquals($data['twitter_link'], $lPSpeaker->twitter_link);
    }

    public function testGetLandingPageSpeakers()
    {
        $congress = factory(Congress::class)->create();
        $lPSpeaker = factory(LPSpeaker::class)->create(['congress_id' => $congress->congress_id]);
        $this->get('/api/admin/me/congress/' . $congress->congress_id . '/landing-page/get-speakers')
            ->assertStatus(200);
    }

    public function testEditLandingPageSpeaker()
    {
        $congress = factory(Congress::class)->create();
        $oldLPSpeaker = factory(LPSpeaker::class)->create(['congress_id' => $congress->congress_id]);
        $data = $this->getLPSpeaker();

        $response = $this->post('/api/landing-page-speakers/edit/' . $oldLPSpeaker->lp_speaker_id, $data)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);

        $lPSpeaker = LPSpeaker::where('congress_id', '=', $dataResponse['congress_id'])
            ->first();

        $this->assertEquals($congress->congress_id, $dataResponse['congress_id']);
        $this->assertEquals($data['first_name'], $lPSpeaker->first_name);
        $this->assertEquals($data['last_name'], $lPSpeaker->last_name);
        $this->assertEquals($data['role'], $lPSpeaker->role);
        $this->assertEquals($data['profile_img'], $lPSpeaker->profile_img);
        $this->assertEquals($data['fb_link'], $lPSpeaker->fb_link);
        $this->assertEquals($data['linkedin_link'], $lPSpeaker->linkedin_link);
        $this->assertEquals($data['instagram_link'], $lPSpeaker->instagram_link);
        $this->assertEquals($data['twitter_link'], $lPSpeaker->twitter_link);
    }

    public function testDeleteLandingPageSpeaker()
    {
        $congress = factory(Congress::class)->create();
        $lPSpeaker = factory(LPSpeaker::class)->create(['congress_id' => $congress->congress_id]);
        $this->delete('/api/landing-page-speakers/delete/' . $lPSpeaker->lp_speaker_id)
            ->assertStatus(200);
    }

    public function testSyncronizeLandingPage1()
    {
        $congress = factory(Congress::class)->create();
        $configCongress = factory(ConfigCongress::class)->create(['congress_id' => $congress->congress_id]);
        $this->get('/api/admin/me/congress/' . $congress->congress_id . '/landing-page/syncronize')
            ->assertStatus(200);
    }


    public function testSyncronizeLandingPage2()
    {
        $congress = factory(Congress::class)->create();
        $config_congress = factory(ConfigCongress::class)->create(['congress_id' => $congress->congress_id]);
        $configLP = factory(ConfigLP::class)->create(['congress_id' => $congress->congress_id]);
        $response = $this->get('/api/admin/me/congress/' . $congress->congress_id . '/landing-page/syncronize')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);

        $configLP = ConfigLP::where('congress_id', '=', $dataResponse['config_landing_page']['congress_id'])
            ->first();

        $this->assertEquals($congress->congress_id, $dataResponse['config_landing_page']['congress_id']);
        $this->assertEquals($config_congress->logo, $configLP->header_logo_event);
        $this->assertEquals($congress->name, $configLP->home_title);
        $this->assertEquals($congress->description, $configLP->home_description);
        $this->assertEquals($config_congress->banner, $configLP->home_banner_event);
        $this->assertEquals($config_congress->banner, $configLP->prp_banner_event);
    }

    private function getFakeDataCongress()
    {
        return [
            'name' => $this->faker->sentence,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'price' => $this->faker->randomFloat(2, 0, 5000),
            'congress_type_id' => strval($this->faker->numberBetween(1, 3)),
            'private' => $this->faker->numberBetween(0, 1),

            'description' => $this->faker->paragraph,
            'config' => [
                'free' => $this->faker->numberBetween(0, 100),
                'access_system' => 'Workshop',
                'prise_charge_option' => $this->faker->numberBetween(0, 1),
                'is_online' => $this->faker->numberBetween(0, 1),
                'status' => 1,
                'is_submission_enabled' => $this->faker->numberBetween(0, 1),
                'currency_code' => 'TND',
            ],
            'config_selection' => [
                'num_evaluators' => $this->faker->numberBetween(1, 20),
                'selection_type' =>  $this->faker->numberBetween(0, 2),
                'start_date' => $this->faker->date(),
                'end_date' => $this->faker->date(),
            ],
        ];
    }

    private function getConfigLP()
    {
        return [
            'header_logo_event' => Utils::generateCode(0, 15) . ".png",
            'is_inscription' => $this->faker->numberBetween(0, 1),
            'home_banner_event' => Utils::generateCode(0, 15) . ".png",
            'home_start_date' => $this->faker->dateTime(),
            'home_end_date' => $this->faker->dateTime(),
            'home_title' => $this->faker->sentence,
            'home_description' => $this->faker->paragraph,
            'prp_banner_event' => Utils::generateCode(0, 15) . ".png",
            'prp_title' => $this->faker->sentence,
            'prp_description' => $this->faker->paragraph,
            'speaker_title' => $this->faker->sentence,
            'speaker_description' => $this->faker->paragraph,
            'sponsor_title' => $this->faker->sentence,
            'sponsor_description' => $this->faker->paragraph,
            'prg_title' => $this->faker->sentence,
            'prg_description' => $this->faker->paragraph,
            'contact_title' => $this->faker->sentence,
            'contact_description' => $this->faker->paragraph,
            'event_link_fb' => $this->faker->url,
            'event_link_instagram' => $this->faker->url,
            'event_link_linkedin' => $this->faker->url,
            'event_link_twitter' => $this->faker->url
        ];
    }

    private function getLPSpeaker()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'role' => $this->faker->word,
            'profile_img' => Utils::generateCode(0, 15) . ".png",
            'fb_link' => $this->faker->url,
            'linkedin_link' => $this->faker->url,
            'instagram_link' => $this->faker->url,
            'twitter_link' => $this->faker->url,
        ];
    }
}
