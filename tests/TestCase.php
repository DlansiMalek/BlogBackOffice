<?php

namespace Tests;

use App\Models\Admin;
use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $faker;
    protected $clientToken;
    protected $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->setClientToken();

        $this->withHeaders(['HTTP_Authorization' => 'Bearer ' . $this->clientToken]);
    }

    private function setClientToken()
    {
        $client = factory(Admin::class)->create(['privilege_id' => 1]);

        $this->admin = $client;

        $this->clientToken = JWTAuth::fromUser($client);
    }

}
