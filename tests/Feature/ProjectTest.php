<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Project;
use App\Services\Utils;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProjectTest extends TestCase
{

    public function testGetProject()
    {
        $category = factory(Category::class)->create();
        $project = factory(Project::class)->create(['category_id' => $category->category_id, 'admin_id' => $this->admin->admin_id,
        ]);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $response = $this->get('api/project/list')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertEquals($dataResponse[count($dataResponse) - 1]["nom"], $project->nom);
        $this->assertEquals($dataResponse[count($dataResponse) - 1]["date"], $project->date->format('Y-m-d'));
        $this->assertEquals($dataResponse[count($dataResponse) - 1]["lien"], $project->lien);
        $this->assertEquals($dataResponse[count($dataResponse) - 1]['project_img'], $project->project_img);
    }
    public function testDeleteProject()
    {
        $category = factory(Category::class)->create();
        $project = factory(Project::class)->create(['category_id' => $category->category_id, 'admin_id' => $this->admin->admin_id]);
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $response = $this->delete('api/project/delete/' . $project->project_id)
            ->assertStatus(200);
    }
    public function testAddProject()
    {
        $category = factory(Category::class)->create();
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $data = $this->getFakeProject($category->category_id, $this->admin->admin_id);
        $response = $this->post('api/project/add', $data)
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $project = Project::where('project_id', '=', $dataResponse['project_id'])
            ->first();
        $this->assertEquals($data['nom'], $project->nom);
        $this->assertEquals($data['date']->format('Y-m-d'), $project->date);
        $this->assertEquals($data['lien'], $project->lien);
        $this->assertEquals($data['project_img'], $project->project_img);

    }
    public function testGetProjectWithId()
    {
        $category = factory(Category::class)->create();
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $project = factory(Project::class)->create(['category_id' => $category->category_id, 'admin_id' => $this->admin->admin_id]);
        $this->get('api/project/get/' . $project->project_id)
            ->assertStatus(200);
    }
    public function testGetProjectWithPagination()
    {
        $category = factory(Category::class)->create();
        $project = factory(Project::class)->create(['category_id' => $category->category_id, 'admin_id' => $this->admin->admin_id,
        ]);
        $response = $this->get('api/project/listWithPagination?perPage=100')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $data = collect($dataResponse['data'])->sortBy('project_id')->reverse()->values();
        $this->assertEquals($data[0]["nom"], $project->nom);
        $this->assertEquals($data[0]["date"], $project->date->format('Y-m-d'));
        $this->assertEquals($data[0]["lien"], $project->lien);
        $this->assertEquals($data[0]["project_img"], $project->project_img);
    }
    private function getFakeProject($category_id, $admin_id)
    {
        return [
            'nom' => $this->faker->word,
            'date' => $this->faker->dateTime(),
            'lien' => $this->faker->word,
            'project_img' => Utils::generateCode(0, 15) . ".png",
            'admin_id' => $admin_id,
            'category_id' => $category_id,
        ];
    }
}
