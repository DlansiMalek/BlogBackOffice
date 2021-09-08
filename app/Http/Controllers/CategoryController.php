<?php

namespace App\Http\Controllers;

use App\Services\CategoryServices;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryServices;

    public function __construct(CategoryServices $categoryServices)
    {
        $this->categoryServices = $categoryServices;

    }

    public function addCategory(Request $request)
    {
        $category = null;
        if ($request->has('category_id')) {
            $category = $this->categoryServices->getCategoryById($request->input('category_id'));
        }
        $this->categoryServices->addCategory($category, $request);
        $categories = $this->categoryServices->getAll();

        return response()->json($categories);
    }

    public function getCategories()
    {

        $categories = $this->categoryServices->getAll();
        return response()->json($categories);
    }

}
