<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Congress;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Admin;
use App\Models\UserMeeting;
use Tymon\JWTAuth\Facades\JWTAuth;

class MeetingTest extends TestCase
{

    public function testGetUserMeetingsById()
    {
        $congress = factory(Congress::class)->create();
        $userSender = factory(User::class)->create();
        $userReceiver = factory(User::class)->create();
        $meeting = factory(Meeting::class)->create([ 'congress_id'=> $congress->congress_id]);
        $UserMeeting = factory(UserMeeting::class)->create(['meeting_id' => $meeting->meeting_id, 'user_sender_id' => $userSender->user_id, 'user_receiver_id' => $userReceiver->user_id]);
        $response = $this->get('api/meetings/'.$congress->congress_id.'/?user_id=' . $userSender->user_id);
        $response->assertStatus(200);
    }

    public function testAddUserMeeting()
    {
        $congress = factory(Congress::class)->create();
        $userSender = factory(User::class)->create();
        $userReceiver = factory(User::class)->create();
        $meeting = $this->getFakeMeeting();

        $token = JWTAuth::fromUser($userSender);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/meetings/add?congress_id=' . $congress->congress_id . '&user_received_id=' . $userReceiver->user_id, $meeting)
            ->assertStatus(200);
    }

    public function testUpdateUserMeetingStatus()
    {
        $congress = factory(Congress::class)->create();
        $userSender = factory(User::class)->create();
        $userReceiver = factory(User::class)->create();
        $meeting = factory(Meeting::class)->create([ 'congress_id'=> $congress->congress_id]);
        $UserMeeting = factory(UserMeeting::class)->create(['meeting_id' => $meeting->meeting_id, 'user_sender_id' => $userSender->user_id, 'user_receiver_id' => $userReceiver->user_id]);
        $status =1;
        $token = JWTAuth::fromUser($userSender);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/meetings/update?congress_id=' . $congress->congress_id . '&user_received_id=' . $userReceiver->user_id.'&meeting_id='.$meeting->meeting_id. '&status='.$status.'&user_canceler='.$userSender->user_id)
            ->assertStatus(200);
    }


    private function getFakeMeeting()
    {
        return [
            'name' =>    $this->faker->word,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ];
    }
}
