<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Admin;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryTest extends TestCase
{
    public function testGetCategories()
    {
        $category = factory(Category::class)->create();
        $response = $this->get('api/category/list')
            ->assertStatus(200);
        $dataResponse = json_decode($response->getContent(), true);
        $this->assertEquals($dataResponse[count($dataResponse) - 1]["label"], $category->label);
    }
    
    public function testAddCategory()
    {
        $data = $this->getFakeCategory();
        $superAdmin = factory(Admin::class)->create(['privilege_id' => 9]);
        $token = JWTAuth::fromUser($superAdmin);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $response = $this->post('api/category/add', $data)
            ->assertStatus(200);       
        $dataResponse = json_decode($response->getContent(), true);
        $category = Category::where('category_id', '=', $dataResponse[count($dataResponse) - 1]["category_id"])->first();
        $this->assertEquals($data['label'],$category->label);
    }

    private function getFakeCategory()
    {
        return [
            'label' => $this->faker->word,
        ];
    }
}
