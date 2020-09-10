<?php

namespace Tests\Feature;

use App\Models\Congress;
use App\Models\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testGetById()
    {
        $mail = factory(Mail::class)->create();
        $this->get('api/congress/mail/' .$mail->mail_id)
            ->assertStatus(200);
    }
    public function testGetMailTypeById()
    {
        $mail = factory(Mail::class)->create();
        $this->get('api/congress/mail/types/' .$mail->mail_type_id)
            ->assertStatus(200);
    }

    public function testGetAllMailTypes()
    {
        //  api/congress/mail/types/1
        $congress = factory(Congress::class)->create();
        $this->get('api/congress/' .$congress->congress_id .'/mail/types')
            ->assertStatus(200);
    }
}
