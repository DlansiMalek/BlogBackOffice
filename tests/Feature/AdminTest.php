<?php

namespace Tests\Feature;

use App\Models\ConfigCongress;
use App\Models\Congress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetAttestationDiversByCongress()
    {
        $congress = factory(Congress::class)->create();
        $this->get('api/admin/me/congress/' . $congress->congress_id .'/attestation-divers')
            ->assertStatus(200);
    }

    // fail
    public function testEditStatus()
    {
        $status = true;
        $congress = factory(Congress::class)->create();
        $configCongres = factory(ConfigCongress::class)->create(['congress_id' => $congress->congress_id]);;
        $this->get('/api/admin/me/congress/' . $congress->congress_id .'/edit-status/' . $status )
            ->assertStatus(200);
    }
}
