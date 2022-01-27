<?php

namespace App\Services;

use App\Models\Project;

class ProjectServices
{
    public function getAll()
    {
        return Project::with(['admin', 'category'])->get();

    }

    public function getProjectById($project_id)
    {
        return Project::find($project_id);
    }

    public function deleteProject($Project)
    {
        return $Project->delete();
    }

    public function addProject($project, $request)
    {
        if (!$project) {
            $project = new Project();
        }
        $project->nom = $request['nom'];
        $project->date = $request['date'];
        $project->project_img = $request['project_img'];
        $project->lien = $request['lien'];
        $project->admin_id = $request['admin_id'];
        $project->category_id = $request['category_id'];
        $project->save();
        return $project;
    }

    public function getProjectPagination($perPage, $category_id)
    {
        $allProject = Project::with(['admin', 'category'])->where(function ($query) use ($category_id) {
            if ($category_id != 0) {
                $query->where('category_id', '=', $category_id);
            }
        });
        return $allProject->paginate($perPage);
    }

}
