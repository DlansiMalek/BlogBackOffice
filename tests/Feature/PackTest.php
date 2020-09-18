<?php

namespace Tests\Feature;

use App\Models\Congress;
use App\Models\Pack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PackTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetAllPackByCongress()
    {
       $pack = factory(Pack::class)->create();

       $this->get('/api/pack/congress/'.$pack->congress_id .'/list')
            ->assertStatus(200);

    }

}
