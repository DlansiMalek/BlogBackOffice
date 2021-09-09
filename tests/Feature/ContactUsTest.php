<?php

namespace Tests\Feature;

use App\Models\ContactUs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class ContactUsTest extends TestCase
{

  public function testSendContactUs()
  {
    $contact =  $this->getFakeContact();
    $response =  $this->post('api/contact-us/send', $contact)
      ->assertStatus(200);
  }

  private function getFakeContact()
  {
    return [
      'email' => $this->faker->email,
      'user_name' =>  $this->faker->word,
      'subject'   =>  $this->faker->word,
      'message'  =>  $this->faker->sentence,
      'mobile' =>  $this->faker->phoneNumber,
    ];
  }
}
