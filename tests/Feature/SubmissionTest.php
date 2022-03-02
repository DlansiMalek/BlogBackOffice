<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Congress;
use App\Models\Resource;
use App\Models\ResourceSubmission;
use App\Models\Submission;
use App\Models\User;
use App\Models\ConfigSubmission;
use App\Models\ConfigCongress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetAllSubmissionsByCongress()
    {
        $congress = factory(Congress::class)->create();
        $user = factory(User::class)->create();
        $submission = factory(Submission::class)->create([
            'congress_id' => $congress->congress_id,
            'user_id' => $user->user_id,
            'type' => 'Série',
            'communication_type_id' => 1,
            'theme_id' => 1,
        ]);
        $author = factory(Author::class)->create([
            'submission_id' => $submission->submission_id,
            'service_id' => 1,
            'etablissement_id' => 1,
            'rank' => 1
        ]);
        $resource = factory(Resource::class)->create();
        $resrceSubmission = factory(ResourceSubmission::class)->create([
            'submission_id' => $submission->submission_id,
            'resource_id' => $resource->resource_id
        ]);
        $this->get('api/submissions/congress/' . $congress->congress_id.'?communication_type_id=1&theme_id=1')
            ->assertStatus(200);

    }

    public function testAddConfigSubmission()
    {
        $congress = factory(Congress::class)->create();
        // in case of error in this test check if there is an input in ConfigCongress that can't be null and add it here
        $config = factory(ConfigCongress::class)->create([
            'congress_id' => $congress->congress_id,
            'is_submission_enabled' => 1,
            'program_link' => 'https://eventizer.io',
            'has_payment' => $this->faker->numberBetween(0, 1),
            'is_online' => $this->faker->numberBetween(0, 1),
            'is_code_shown' => $this->faker->numberBetween(0, 1),
            'is_notif_register_mail' => $this->faker->numberBetween(0, 1),
            'register_disabled' => $this->faker->numberBetween(0, 1),
            'is_notif_sms_confirm' => $this->faker->numberBetween(0, 1),
            'is_submission_enabled' => $this->faker->numberBetween(0, 1),
            'application' => $this->faker->numberBetween(0, 1),
            'nb_current_participants' => $this->faker->numberBetween(0, 1),
            'max_online_participants' => $this->faker->numberBetween(0, 1),
            'is_upload_user_img' => $this->faker->numberBetween(0, 1),
            'is_sponsor_logo' => $this->faker->numberBetween(0, 1),
            'is_phone_required' => $this->faker->numberBetween(0, 1),
            'mobile_technical' => $this->faker->phoneNumber,
            'nb_max_access' => $this->faker->numberBetween(-1, 10),
            'meeting_duration' => $this->faker->numberBetween(0, 60),
            'pause_duration' => $this->faker->numberBetween(0, 30),
            'nb_meeting_table' => $this->faker->numberBetween(0, 1),
        ]);
        $config['privileges'] = [3];
        $submission = $this->getDataSubmission();
        $request = ['congress' => $config, 'submission' => $submission];
        $this->post('/api/admin/me/congress/' . $congress->congress_id . '/edit-config', $request)
            ->assertStatus(200);

        $configSubmission = ConfigSubmission::where('congress_id', '=', $congress->congress_id)->first();
        $this->assertEquals($configSubmission->max_words, $submission['max_words']);
        $this->assertEquals($configSubmission->num_evaluators, $submission['num_evaluators']);
    }

    public function testDeleteConfigSubmission()
    {
        $congress = factory(Congress::class)->create();
        // in case of error in this test check if there is an input in ConfigCongress that can't be null and add it here
        $config = factory(ConfigCongress::class)->create([
            'congress_id' => $congress->congress_id,
            'is_submission_enabled' => 0,
            'program_link' => 'https://eventizer.io',
            'has_payment' => $this->faker->numberBetween(0, 1),
            'is_online' => $this->faker->numberBetween(0, 1),
            'is_code_shown' => $this->faker->numberBetween(0, 1),
            'is_notif_register_mail' => $this->faker->numberBetween(0, 1),
            'register_disabled' => $this->faker->numberBetween(0, 1),
            'is_notif_sms_confirm' => $this->faker->numberBetween(0, 1),
            'is_submission_enabled' => $this->faker->numberBetween(0, 1),
            'application' => $this->faker->numberBetween(0, 1),
            'nb_current_participants' => $this->faker->numberBetween(0, 1),
            'max_online_participants' => $this->faker->numberBetween(0, 1),
            'is_upload_user_img' => $this->faker->numberBetween(0, 1),
            'is_sponsor_logo' => $this->faker->numberBetween(0, 1),
            'is_phone_required' => $this->faker->numberBetween(0, 1),
            'mobile_technical' => $this->faker->phoneNumber,
            'nb_max_access' => $this->faker->numberBetween(-1, 10),
            'meeting_duration' => $this->faker->numberBetween(0, 60),
            'pause_duration' => $this->faker->numberBetween(0, 30),
            'nb_meeting_table' => $this->faker->numberBetween(0, 1),

        ]);
        $config['privileges'] = [3];
        $submission = factory(ConfigSubmission::class)->create(['congress_id' => $congress->congress_id]);
        $request = ['congress' => $config, 'submission' => []];
        $this->post('/api/admin/me/congress/' . $congress->congress_id . '/edit-config', $request)
            ->assertStatus(200);

        $configSubmission = ConfigSubmission::where('congress_id', '=', $congress->congress_id)->first();
        // make sure that there isn't any ConfigSubmission
        $this->assertNull($configSubmission);
    }

    public function testMakeMassSubmissionEligible()
    {
        $congress = factory(Congress::class)->create();
        $user = factory(User::class)->create();
        $submission = factory(Submission::class)->create([
            'congress_id' => $congress->congress_id,
            'user_id' => $user->user_id,
            'type' => 'Série',
            'communication_type_id' => 1,
            'status' => 1,
            'eligible' => 0
        ]);
        $request[] =  $submission->submission_id;
        $eligibilityFalse = "false";
        $this->put('/api/submission/make_eligible/' . $congress->congress_id . '/' . $eligibilityFalse, $request)
            ->assertStatus(200);
        $eligibilityTrue = "true";
        $this->put('/api/submission/make_eligible/' . $congress->congress_id . '/' . $eligibilityTrue, $request)
            ->assertStatus(200);
    }

    private function getDataSubmission()
    {
        return [
            'end_submission_date' => $this->faker->date(),
            'start_submission_date' => $this->faker->date(),
            'max_words' => $this->faker->numberBetween(100, 500),
            'num_evaluators' => $this->faker->numberBetween(1, 5),
            'show_file_upload' => $this->faker->numberBetween(0, 1),
            'explanatory_paragraph' => $this->faker->paragraph
        ];
    }
}
