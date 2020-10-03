<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Congress;
use App\Models\Resource;
use App\Models\ResourceSubmission;
use App\Models\Submission;
use App\Models\User;
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
            'type' =>'Série'
        ]);
        $author = factory(Author::class)->create([
          'submission_id' => $submission->submission_id
        ]);
        $resource = factory(Resource::class)->create();
        $resrceSubmission = factory(ResourceSubmission::class)->create([
            'submission_id' => $submission->submission_id,
            'resource_id' => $resource->resource_id
        ]);
        $this->get('api/submissions/congress/'. $congress->congress_id)
            ->assertStatus(200);

    }
}
