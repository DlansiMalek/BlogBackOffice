<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Offre;
use App\Models\PaymentAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OffreTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAddOffreFail()
    {
        $offre = [
            'nom' => $this->faker->word,
            'value' => $this->faker->numberBetween(500,1000),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ];
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/offre/add', $offre)
            ->assertStatus(400);
    }

    public function testAddOffre()
    {
        $admin = factory(Admin::class)->create(['privilege_id' => 1]);
        $offre = $this->createOffre($admin->admin_id);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/offre/add', $offre)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);

        $newOffre = Offre::where('offre_id', '=', $dataResponse['offre']['offre_id'])->first();
        $this->assertEquals($offre['nom'], $newOffre->nom);
        $this->assertEquals($offre['start_date'], $newOffre->start_date);
        $this->assertEquals($offre['end_date'], $newOffre->end_date);
        $this->assertEquals($offre['type_id'], $newOffre->type_id);
        $this->assertEquals($offre['admin_id'], $newOffre->admin_id);
    }

    public function testEditOffre()
    {
        $admin = factory(Admin::class)->create(['privilege_id' => 1]);
        $offre = $this->createOffre($admin->admin_id);
        $offreOld = factory(Offre::class)->create(['admin_id' => $admin->admin_id, 'status' => 1]);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->put('api/offre/edit/' . $offreOld->offre_id, $offre)
            ->assertStatus(200);

        $dataResponse = json_decode($response->getContent(), true);

        $newOffre = Offre::where('offre_id', '=', $dataResponse['offre']['offre_id'])->first();
        $this->assertEquals($offreOld->offre_id, $dataResponse['offre']['offre_id']);
        $this->assertEquals($offre['nom'], $newOffre->nom);
        $this->assertEquals($offre['start_date'], $newOffre->start_date);
        $this->assertEquals($offre['end_date'], $newOffre->end_date);
        $this->assertEquals($offre['type_id'], $newOffre->type_id);
        $this->assertEquals($offre['admin_id'], $newOffre->admin_id);
    }

    public function testGetOffreById()
    {
        $admin = factory(Admin::class)->create(['privilege_id' => 1]);
        $offre = factory(Offre::class)->create(['admin_id'=> $admin->admin_id, 'status' => 1]);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/offre/get/' . $offre->offre_id)
            ->assertStatus(200);
    }

    public function createOffre($admin_id){
        return [
            'nom' => $this->faker->word,
            'value' => $this->faker->numberBetween(500,1000),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'type_id' =>$this->faker->numberBetween(1,4),
            'admin_id' =>$admin_id,
        ];
    }
}
