<?php

namespace App\Services;

use App\Models\Category;

class CategoryServices
{
    public function getAll()
    {
        return Category::all();
    }

    public function addCategory($category, $request)
    {
        if (!$category) {
            $category = new Category();
        }
        $category->label = $request['label'];
        $category->save();
        return $category;
    }

    public function getCategoryById($category_id)
    {
        return Category::find($category_id);
    }
}
