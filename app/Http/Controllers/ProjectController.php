<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\CategoryServices;
use App\Services\ProjectServices;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $projectServices;
    protected $categoryServices;
    protected $adminServices;

    public function __construct(ProjectServices $projectServices, CategoryServices $categoryServices, AdminServices $adminServices)
    {
        $this->projectServices = $projectServices;
        $this->categoryServices = $categoryServices;
        $this->adminServices = $adminServices;

    }

    public function addProject(Request $request)
    {

        if (!$category = $this->categoryServices->getCategoryById($request->input('category_id'))) {
            return response()->json(["message" => "category not found"], 404);
        }

        if (!$admin = $this->adminServices->getAdminById($request->input('admin_id'))) {
            return response()->json(['message' => 'admin not found'], 404);
        }

        $project = null;
        if ($request->has('project_id')) {
            $project = $this->projectServices->getProjectById($request->input('project_id'));
        }
        $project = $this->projectServices->addProject($project, $request);

        return response()->json($project);
    }

    public function getProjects()
    {
        $projects = $this->projectServices->getAll();
        return response()->json($projects);
    }

    public function getProjectWithId($project_id)
    {
        $project = $this->projectServices->getProjectById($project_id);
        return response()->json($project);
    }

    public function deleteProject($project_id)
    {
        if (!$project = $this->projectServices->getProjectById($project_id)) {
            return response()->json(['error' => 'project not found'], 404);
        }

        $this->projectServices->deleteProject($project);
        return response()->json($this->projectServices->getAll());
    }

    public function getProjectPagination(Request $request)
    {
        $perPage = $request->query('perPage', 6);
        $category_id = $request->query('category_id', '');
        $projects = $this->projectServices->getProjectPagination($perPage, $category_id);
        return response()->json($projects);
    }

}
